<?php
namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Enums\UserRole;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|string|unique:users|max:20|regex:/^BCL-\w{8}$/',
            'username' => 'required|string|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date|before:-13 years',
        ], [
            'member_id.regex' => 'Member ID must be in BCL-XXXXXXX format',
            'date_of_birth.before' => 'You must be at least 13 years old'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'member_id' => $request->member_id,
            'uuid' => Str::uuid(),
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::MEMBER->value,
            'approval_status' => 'pending',
            'date_of_birth' => $request->date_of_birth,
        ]);

        return response()->json([
            'message' => 'Registration successful! Pending moderator approval.',
            'user' => [
                'uuid' => $user->uuid,
                'member_id' => $user->member_id,
                'email' => $user->email,
                'created_at' => $user->created_at
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required_without:member_id|email|nullable',
            'member_id' => 'required_without:email|string|nullable',
            'password' => 'required',
            'remember_me' => 'sometimes|boolean'
        ]);

        // Determine login identifier (email or member_id)
        $identifier = $request->filled('email') 
            ? 'email' 
            : 'member_id';

        if (!Auth::attempt([$identifier => $credentials[$identifier], 'password' => $credentials['password']])) {
            return response()->json([
            'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        
        // Handle approval status
        if ($user->approval_status === 'pending') {
            Auth::logout();
            return response()->json([
                'message' => 'Account pending moderator approval',
                'approval_status' => 'pending'
            ], 403);
        }
        
        if ($user->approval_status === 'rejected') {
            Auth::logout();
            return response()->json([
                'message' => 'Account rejected by moderators',
                'approval_status' => 'rejected'
            ], 403);
        }

        // Set token expiration (30 days if remember_me, 2 hours otherwise)
        $expiration = $request->remember_me 
            ? Carbon::now()->addDays(30) 
            : Carbon::now()->addHours(2);

        $token = $user->createToken(
            'auth_token', 
            ['*'], 
            $expiration
        )->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiration->toDateTimeString(),
            'user' => [
                'uuid' => $user->uuid,
                'member_id' => $user->member_id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }

    public function logout(Request $request)
    {
      $token = $request->user()->token();
if ($token) {
    $token->revoke();
}
        
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}