<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class RegisterController extends Controller
{

    //api function for user registration
    public function register(Request $request)
    {
        //log
        Log::info('API register called', [
            'email' => $request->input('email'),
            'path'  => $request->path(),
            'method'=> $request->method(),
        ]);

        $data = $request->validate([
            'name' => ['required','string','max:255','unique:users,name'],
            'email' => ['required','email','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'role' => ['required','in:student,teacher'],
        ]);

        $user = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>Hash::make($data['password']),
            'role'=>$data['role'],
        ]);

        $token = $user->createToken('api-token', ["role:{$user->role}"])->plainTextToken;

        return response()->json(['message'=>'Registered','user'=>$user,'token'=>$token], 201);
    }
}
