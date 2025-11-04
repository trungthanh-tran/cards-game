<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Don't forget to include the Log facade

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::error('Full Request Payload', [
                'data' => $request->all(), // Logs ALL fields
                'ip' => $request->ip()
            ]);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            // --- 1. Log Validation Failure ---
            // Log the failure details, including the email and specific errors.
            Log::warning('User Registration Failed Validation', [
                'email' => $request->email,
                'errors' => $validator->errors()->toArray(),
                'ip' => $request->ip()
            ]);
            
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            // --- 2. Log Successful Registration ---
            // Log the successful creation of the new user's account.
            Log::info('New User Registered Successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 201);
            
        } catch (\Exception $e) {
            // --- 3. Log Database/Creation Exception ---
            // Log a critical error if the User::create or token creation fails unexpectedly.
            Log::error('Critical Registration Error for email: ' . $request->email, [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Return a generic error response to the client
            return response()->json(['message' => 'Registration failed due to a server error.'], 500);
        }
    }

    public function login(Request $request)
    {
        // Log the incoming login attempt
        Log::info('Login attempt initiated', ['email' => $request->email, 'ip' => $request->ip()]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            // Log validation failure
            Log::warning('Login validation failed', ['email' => $request->email, 'errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Log authentication failure
            $message = 'Thông tin đăng nhập không chính xác';
            Log::warning('Login authentication failed (Invalid credentials)', ['email' => $request->email]);
            return response()->json(['message' => $message], 401);
        }

        if (!$user->is_active) {
            // Log inactive account block
            $message = 'Tài khoản đã bị khóa';
            Log::warning('Login failed (Account locked/inactive)', ['user_id' => $user->id, 'email' => $user->email]);
            return response()->json(['message' => $message], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Log successful login
        Log::info('User logged in successfully', ['user_id' => $user->id, 'email' => $user->email]);

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đăng xuất thành công']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('wallet'));
    }
}