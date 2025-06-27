<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'username' => ['required', 'string', 'max:50', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:200', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'fullname' => ['nullable', 'string', 'max:100'],
            ]);

            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password), 
                'fullname' => $request->fullname,
            ]);

            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully!',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'fullname' => $user->fullname,
                ],
                'token' => $token,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'email' => ['Invalid credentials.'],
                ]);
            }

            $user = $request->user();

            $user->tokens()->delete(); 
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Login successful!',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'fullname' => $user->fullname,
                ],
                'token' => $token,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Authentication Failed',
                'errors' => $e->errors(),
            ], 401); 
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully!'], 200);
    }
}
