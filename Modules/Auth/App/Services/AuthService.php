<?php

namespace Modules\Auth\App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\StatefulGuard;
use Modules\Auth\App\Contracts\AuthServiceInterface;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    protected StatefulGuard $guard;

    public function __construct(AuthFactory $auth)
    {
        $this->guard = $auth->guard();
    }

    public function register(array $data): array
    {
        $user = User::create([
            'member_id' => $data['member_id'],
            'uuid' => Str::uuid(),
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::MEMBER->value,
            'approval_status' => 'pending',
            'date_of_birth' => $data['date_of_birth'],
        ]);

        return [
            'message' => 'Registration successful! Pending moderator approval.',
            'user' => $user->only(['uuid', 'member_id', 'email', 'created_at'])
        ];
    }

    public function login(array $credentials): array
    {
        $identifier = isset($credentials['email']) ? 'email' : 'member_id';
        $attemptCredentials = [
            $identifier => $credentials[$identifier],
            'password' => $credentials['password']
        ];

        if (!$this->guard->attempt($attemptCredentials)) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid credentials']
            ])->status(401);
        }

        $user = $this->guard->user();
        
        if ($user->approval_status === 'pending') {
            $this->guard->logout();
            abort(403, 'Account pending moderator approval');
        }
        
        if ($user->approval_status === 'rejected') {
            $this->guard->logout();
            abort(403, 'Account rejected by moderators');
        }

        $expiration = $credentials['remember_me'] ?? false 
            ? Carbon::now()->addDays(30) 
            : Carbon::now()->addHours(2);

        $token = $user->createToken(
            'auth_token', 
            ['*'], 
            $expiration
        )->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiration->toDateTimeString(),
            'user' => $user->only(['uuid', 'member_id', 'username', 'email', 'role'])
        ];
    }

    public function logout(): void
    {
        // Get the authenticated user first
        $user = $this->guard->user();
        
        if ($user) {
            $user->token()->revoke();
        }
    }

    public function getPendingUsers(): array
    {
        return User::where('approval_status', 'pending')
            ->get(['uuid', 'member_id', 'email', 'username', 'created_at'])
            ->toArray();
    }

    public function approveUser(User $user): array
    {
        if ($user->approval_status !== 'pending') {
            abort(400, 'User is not pending approval');
        }

        $user->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);

        return [
            'message' => 'User approved successfully',
            'user' => $user->only(['uuid', 'email', 'approval_status', 'approved_at'])
        ];
    }

    public function rejectUser(User $user): void
    {
        if ($user->approval_status !== 'pending') {
            abort(400, 'User is not pending approval');
        }

        $user->update([
            'approval_status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }
}