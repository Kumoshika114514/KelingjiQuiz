<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\AccountSuspended;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $email = (string) $request->input('email');
        $user = User::where('email', $email)->first();

        $failuresKey = 'login:failed:' . strtolower($email);
        $suspendKey = $user ? 'login:suspend:user:' . $user->id : null;

        // 1) Early suspension check 
        if ($user && $suspendKey && Cache::has($suspendKey)) {
            $until = Cache::get($suspendKey);
            $untilCarbon = $until instanceof Carbon ? $until : Carbon::parse($until);

            if (Carbon::now()->lt($untilCarbon)) {
                $remaining = Carbon::now()->diffInSeconds($untilCarbon);
                $mins = floor($remaining / 60);
                $secs = $remaining % 60;
                $message = $mins > 0
                    ? "Your account is temporarily suspended for another {$mins} minute(s) and {$secs} second(s). Please try again later."
                    : "Your account is temporarily suspended for another {$secs} second(s). Please try again later.";

                return back()->withErrors(['email' => $message])->withInput($request->except('password'));
            } else {
                Cache::forget($suspendKey);
                Cache::forget($failuresKey);
            }
        }

        // 2) Attempt authentication
        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            if ($user) {
                $duration = $this->handleFailedAttempt($user, $failuresKey, $suspendKey);
                if ($duration) {
                    return back()->withErrors([
                        'email' => "Your account has been temporarily suspended for {$duration} minute(s) due to multiple failed login attempts. We've sent an email notification to the account owner.",
                    ])->withInput($request->except('password'));
                }
            }

            return back()->withErrors(['email' => 'Incorrect username or password. Please try again.'])
                ->withInput($request->except('password'));
        }

        // Regenerate session for security 
        $request->session()->regenerate();

        // 3) Role check — normalize values
        $selectedRole = strtolower(trim((string) $request->input('role', '')));
        $userRole = strtolower(trim((string) (auth()->user()->role ?? '')));

        // Debug log 
        \Log::info('Login role check', [
            'email' => $email,
            'selectedRole' => $selectedRole,
            'userRole' => $userRole,
        ]);

        // If role mismatch -> count failure (same logic)
        if ($selectedRole !== $userRole) {
            if ($user) {
                $duration = $this->handleFailedAttempt($user, $failuresKey, $suspendKey);
            } else {
                $duration = null;
            }

            Auth::guard('web')->logout();
            $request->session()->regenerateToken();

            if (!empty($duration)) {
                return back()->withErrors([
                    'email' => "Your account has been temporarily suspended for {$duration} minute(s) due to multiple failed login attempts. We've sent an email notification to the account owner.",
                ])->withInput($request->except('password'));
            }

            return back()->withErrors([
                'role' => 'Selected role does not match the role associated with this account.',
            ])->withInput($request->except('password'));
        }

        // 4) Now login is fully successful (credentials + role), and clear counters
        if ($user) {
            Cache::forget($failuresKey);
            if ($suspendKey)
                Cache::forget($suspendKey);
        }

        // store role and redirect
        session(['role' => $userRole]);

        return redirect()->intended(match ($userRole) {
            'teacher' => route('teacher.dashboard', absolute: false),
            'student' => route('dashboard', absolute: false),
        });
    }



    /**
     * Handle a failed login-related attempt (password or role mismatch).
     * Increments the failure counter and, if thresholds reached, sets a suspension and notifies the user.
     */
    protected function handleFailedAttempt(User $user, string $failuresKey, ?string $suspendKey): ?int
    {
        $decayMinutes = 60;

        // Use get/put so it works with file/database/cache drivers (not only increment-capable drivers)
        $attempts = (int) Cache::get($failuresKey, 0);
        $attempts++;
        Cache::put($failuresKey, $attempts, now()->addMinutes($decayMinutes));

        // log attempt for debugging
        Log::info('Failed login-related attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'attempts' => $attempts,
        ]);

        // thresholds: attempt => suspension minutes
        $thresholds = [
            4 => 3, // at attempt 4 => 3 mins
            5 => 5, // at attempt 5 => 5 mins
        ];
        $maxSuspension = 7; // minutes for attempts >= 6

        $duration = null;
        if (isset($thresholds[$attempts])) {
            $duration = $thresholds[$attempts];
        } elseif ($attempts >= 6) {
            $duration = $maxSuspension; // user will be suspended for 7 minutes
        }

        if ($duration && $suspendKey) {
            $until = Carbon::now()->addMinutes($duration);
            // store suspension until time; TTL = seconds of duration
            Cache::put($suspendKey, $until, $duration * 60);

            // Try to send email notification
            try {
                Mail::to($user->email)->send(new AccountSuspended($user->name ?? $user->email, $duration));
            } catch (\Throwable $mailEx) {
                // log but continue
                Log::warning('Failed to send suspension email: ' . $mailEx->getMessage());
            }

            // log suspension event
            Log::info('User suspended', [
                'user_id' => $user->id,
                'suspend_minutes' => $duration,
                'until' => $until->toDateTimeString(),
            ]);
        }

        return $duration;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $forgetTheme = cookie()->forget('theme');
        $forgetFont = cookie()->forget('font_size');

        return redirect('/')->withCookies([$forgetTheme, $forgetFont]);
    }
}
