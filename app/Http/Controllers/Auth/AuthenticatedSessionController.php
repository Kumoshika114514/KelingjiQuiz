<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Mail\AccountSuspended;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Events\AccountSuspended as AccountSuspendedEvent;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
     * Handle an incoming authentication request. (including API)
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $email = (string) strtolower($request->input('email', ''));
        $user = User::where('email', $email)->first();

        $failuresKey = 'login:failed:' . $email;
        $suspendKey = $user ? 'login:suspend:user:' . $user->id : null;

        try {
            $apiController = app(LoginController::class);
            $apiResponse = app()->call([$apiController, 'login'], ['request' => $request]);
        } catch (\Throwable $ex) {
            Log::error('Error calling API login from web store(): ' . $ex->getMessage(), [
                'exception' => $ex,
                'email' => $request->input('email'),
            ]);

            return back()->withErrors(['email' => 'An unexpected error occurred. Please try again later.'])
                ->withInput($request->except('password'));
        }

        // --- Normalize the API response into ($status, $data) ---
        $status = 500;
        $data = null;

        // 1) If it's a Response-like object (Illuminate or Symfony), get status and content
        if (is_object($apiResponse) && method_exists($apiResponse, 'getStatusCode')) {
            try {
                $status = (int) $apiResponse->getStatusCode();
                // getContent returns the JSON string
                $content = method_exists($apiResponse, 'getContent') ? $apiResponse->getContent() : null;
                if ($content) {
                    $decoded = json_decode($content, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $decoded;
                    } else {
                        // If content is not json, wrap it
                        $data = ['message' => $content];
                    }
                }
            } catch (\Throwable $ex) {
                Log::warning('Failed to extract content from API response', ['exception' => $ex]);
            }
        } elseif (is_array($apiResponse)) {
            $data = $apiResponse;
            $status = 200;
        } elseif (is_object($apiResponse) && method_exists($apiResponse, 'getData')) {
            try {
                $data = $apiResponse->getData(true);
                $status = method_exists($apiResponse, 'getStatusCode') ? (int) $apiResponse->getStatusCode() : 200;
            } catch (\Throwable $ex) {
                Log::warning('Failed to getData() from API response', ['exception' => $ex]);
            }
        } elseif (is_string($apiResponse)) {
            $data = ['message' => $apiResponse];
            $status = 200;
        }

        // Ensure $data is array
        $data = is_array($data) ? $data : (is_null($data) ? [] : (array) $data);

        /* Log message to prove api used
        Log::info('API login normalized response', ['email' => $email, 'status' => $status, 'data' => $data]);
        */

        // Failure due to invalid credentials or role mismatch
        if (in_array($status, [401, 403], true)) {
            if ($user) {
                $duration = $this->handleFailedAttempt($user, $failuresKey, $suspendKey);

                if ($duration) {
                    return back()->withErrors([
                        'email' => "Your account has been temporarily suspended for {$duration} minute(s) due to multiple failed login attempts. We've sent an email notification to the account owner.",
                    ])->withInput($request->except('password'));
                }
            }

            $message = $data['message'] ?? 'Incorrect username or password. Please try again.';
            return back()->withErrors(['email' => $message])->withInput($request->except('password'));
        }

        // Validation or other non-success
        if ($status !== 200) {
            $message = $data['message'] ?? 'Login failed.';
            $errors = $data['errors'] ?? null;
            if (is_array($errors) && !empty($errors)) {
                return back()->withErrors($errors)->withInput($request->except('password'));
            }
            return back()->withErrors(['email' => $message])->withInput($request->except('password'));
        }

        // Success: expect data['user'] and login the web user
        if (isset($data['user']['id'])) {
            $user = User::find($data['user']['id']) ?: User::where('email', $email)->first();

            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();

                // clear failure counters on successful login
                Cache::forget($failuresKey);
                if ($suspendKey)
                    Cache::forget($suspendKey);

                $userRole = strtolower(trim((string) ($user->role ?? '')));

                return redirect()->intended(match ($userRole) {
                    'teacher' => route('teacher.dashboard', absolute: false),
                    'student' => route('dashboard', absolute: false),
                    default => route('dashboard', absolute: false),
                });
            }

            return back()->withErrors(['email' => 'Login succeeded but local user not found.'])->withInput($request->except('password'));
        }

        // Unexpected payload
        Log::warning('API login returned unexpected payload to web store() (post-normalize)', [
            'email' => $email,
            'status' => $status,
            'payload' => $data,
        ]);

        return back()->withErrors(['email' => 'Unexpected login response.'])->withInput($request->except('password'));
    }




    //original function for handling failed attempts, and sending suspension emails
    protected function handleFailedAttempt(User $user, string $failuresKey, ?string $suspendKey): ?int
    {
        $decayMinutes = 60;

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
            $until = Carbon::now()->addMinutes($duration);
            Cache::put($suspendKey, $until, $duration * 60);

            event(new AccountSuspendedEvent($user, $duration, $until, 'multiple failed login attempts'));

            
            /* Log into laravel.log to show user suspended
            Log::info('User suspended', [
                'user_id' => $user->id,
                'suspend_minutes' => $duration,
                'until' => $until->toDateTimeString(),
            ]);
            */
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

        return redirect('/login')->withCookies([$forgetTheme, $forgetFont]);
    }
}
