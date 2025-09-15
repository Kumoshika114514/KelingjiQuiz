<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{

    //api function for user login
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'sometimes|string',
        ]);

        $email = strtolower($data['email']);
        $user = User::where('email', $email)->first();

        // --- SUSPENSION CHECK HERE ---
        $suspendKey = $user ? 'login:suspend:user:' . $user->id : null;
        $failuresKey = 'login:failed:' . $email;

        if ($user && $suspendKey && Cache::has($suspendKey)) {
            $until = Cache::get($suspendKey);
            $untilCarbon = $until instanceof Carbon ? $until : Carbon::parse($until);

            if (Carbon::now()->lt($untilCarbon)) {
                // if user still suspended
                return response()->json([
                    'message' => 'Account temporarily suspended due to multiple failed login attempts',
                    'suspended_until' => $untilCarbon->toDateTimeString(),
                ], 423);
            } else {
                // expired, and clear suspension and failures
                Cache::forget($suspendKey);
                Cache::forget($failuresKey);
            }
        }

        if (!$user || !Hash::check($data['password'], $user->password)) {
            // Authentication failed 
            return response()->json(['message' => 'Invalid username, password or role. Please login again.'], 401);
        }

        // optional role check
        if (!empty($data['role']) && $data['role'] !== $user->role) {
            return response()->json(['message' => 'Invalid username, password or role. Please login again.'], 403);
        }

        // Success: clear counters and create token
        Cache::forget($failuresKey);
        if ($suspendKey)
            Cache::forget($suspendKey);

        $token = $user->createToken('api-token', ["role:{$user->role}"])->plainTextToken;
        return response()->json(['message' => 'Login successful', 'user' => $user, 'token' => $token]);
    }

    //api function for user logout
    public function logout(Request $request)
    {

        Log::info('API logout called', [
            'user_id' => optional($request->user())->id,
            'path' => $request->path(),
            'method' => $request->method(),
        ]);

        $token = $request->user()?->currentAccessToken();
        if ($token)
            $token->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
