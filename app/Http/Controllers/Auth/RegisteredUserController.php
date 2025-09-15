<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\Auth\RegisterController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

use Illuminate\Validation\Rule;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     */
    public function store(Request $request): RedirectResponse
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:' . User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(['student', 'teacher'])],
        ]);

        try {
            // Resolve API controller instance and call register()
            $apiController = app(RegisterController::class);
            $apiResponse = app()->call([$apiController, 'register'], ['request' => $request]);
        } catch (\Throwable $ex) {
            \Log::error('Error calling API register from web store(): ' . $ex->getMessage(), [
                'exception' => $ex,
                'email' => $request->input('email'),
            ]);

            return back()->withErrors(['email' => 'An unexpected error occurred. Please try again later.'])
                ->withInput($request->except('password'));
        }

        // Normalize apiResponse
        $status = 500;
        $data = [];

        if (is_object($apiResponse) && method_exists($apiResponse, 'getStatusCode')) {
            try {
                $status = (int) $apiResponse->getStatusCode();
                $content = method_exists($apiResponse, 'getContent') ? $apiResponse->getContent() : null;
                if ($content) {
                    $decoded = json_decode($content, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $decoded;
                    } else {
                        $data = ['message' => $content];
                    }
                }
            } catch (\Throwable $ex) {
                \Log::warning('Failed to extract content from API register response', ['exception' => $ex]);
            }
        } elseif (is_array($apiResponse)) {
            $status = 200;
            $data = $apiResponse;
        } elseif (is_object($apiResponse) && method_exists($apiResponse, 'getData')) {
            try {
                $data = $apiResponse->getData(true);
                $status = method_exists($apiResponse, 'getStatusCode') ? (int) $apiResponse->getStatusCode() : 200;
            } catch (\Throwable $ex) {
                \Log::warning('Failed to getData() from API register response', ['exception' => $ex]);
            }
        } elseif (is_string($apiResponse)) {
            $status = 200;
            $data = ['message' => $apiResponse];
        }

        $data = is_array($data) ? $data : [];

        /* Log message to prove api used
        \Log::info('API register normalized response', [
            'email' => $request->input('email'),
            'status' => $status,
            'data' => $data,
        ]);
        */

        // Handle validation / non-success
        if ($status !== 201) {
            $message = $data['message'] ?? 'Registration failed.';
            $errors = $data['errors'] ?? null;

            if (is_array($errors) && !empty($errors)) {
                return back()->withErrors($errors)->withInput($request->except('password'));
            }

            return back()->withErrors(['email' => $message])->withInput($request->except('password'));
        }

        // User registered and log them into the system
        if (isset($data['user']['id'])) {
            $user = User::find($data['user']['id']) ?: User::where('email', $request->input('email'))->first();

            if ($user) {
                event(new Registered($user));
                Auth::login($user);
                $request->session()->regenerate();

                // Redirect based on role
                if ($user->role === 'teacher') {
                    return redirect()->route('teacher.dashboard');
                }

                return redirect(route('dashboard', absolute: false));
            }

        }

        // unexpected fallback
        return back()->withErrors(['email' => 'Unexpected registration response.'])->withInput($request->except('password'));
    }
}
