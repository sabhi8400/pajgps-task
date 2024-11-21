<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/register",
     *     @OA\Response(response="200", description="An example endpoint")
     * )
     */
     // Register new user
     public function register(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'name' => 'required|string|max:255',
             'email' => 'required|string|email|max:255|unique:users',
             'password' => 'required|string|min:8|confirmed',
         ]);

         if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
         }

         $user = User::create([
             'name' => $request->name,
             'email' => $request->email,
             'password' => Hash::make($request->password),
         ]);

         return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
     }

     // Login user and issue token
     public function login(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'email' => 'required|string|email',
             'password' => 'required|string|min:8',
         ]);

         if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
         }

         $user = User::where('email', $request->email)->first();

         if (!$user || !Hash::check($request->password, $user->password)) {
             return response()->json(['message' => 'Invalid credentials'], 401);
         }

         // Issue token
         $token = $user->createToken('YourAppName')->plainTextToken;

         return response()->json([
             'message' => 'Login successful',
             'access_token' => $token,
             'token_type' => 'Bearer',
         ]);
     }

     // Logout user and revoke token
     public function logout(Request $request)
     {
         $request->user()->tokens->each(function ($token) {
             $token->delete();
         });

         return response()->json(['message' => 'Logged out successfully']);
     }

     public function refresh(Request $request)
    {
        // Check the old token exists in the database
        $request->validate([
            'refresh_token' => 'required',
        ]);

        $refreshToken = $request->input('refresh_token');
        $token = PersonalAccessToken::findToken($refreshToken);

        if ($token && $token->created_at->diffInMinutes(now()) < 60) { // Refresh token valid for 60 minutes
            $user = $token->tokenable;

            // Revoke current token
            $token->delete();

            // Create new token
            $newToken = $user->createToken('YourAppName')->plainTextToken;

            return response()->json([
                'access_token' => $newToken,
                'token_type' => 'Bearer',
            ]);
        }

        return response()->json(['error' => 'Invalid or expired refresh token'], 401);
    }
}
