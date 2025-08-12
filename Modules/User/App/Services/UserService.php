<?php

namespace Modules\User\App\Services;

use App\Models\User;
use Modules\User\App\Contracts\UserServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserService implements UserServiceInterface
{
    public function get($id): ?User
    {
        return User::where('id', $id)
            ->orWhere('uuid', $id)
            ->first();
    }

    public function getAll(): array
    {
        return User::select([
            'id',
            'uuid',
            'member_id',
            'username',
            'email',
            'role',
            'approval_status',
            'created_at',
            'approved_at',
            'rejected_at',
            'banned_at'
        ])->get()->toArray();
    }

    public function save(array $data): array
    {
        try {
            $validated = validator($data, [
                'username' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'member_id' => 'sometimes|string|unique:users,member_id',
                'role' => 'sometimes|string|in:admin,moderator,member',
                'date_of_birth' => 'nullable|date',
            ])->validate();

            $user = User::create([
                'uuid' => Str::uuid(),
                'member_id' => $validated['member_id'] ?? 'MEM-' . Str::random(8),
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'] ?? 'member',
                'approval_status' => 'pending',
                'date_of_birth' => $validated['date_of_birth'] ?? null,
            ]);

            return [
                'success' => true,
                'message' => 'User created successfully. Pending approval.',
                'data' => $user->only(['uuid', 'member_id', 'username', 'email', 'role', 'approval_status', 'created_at']),
                'errors' => [],
                'meta' => ['timestamp' => now()->toDateTimeString()]
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors(),
                'meta' => ['timestamp' => now()->toDateTimeString()]
            ];
        }
    }

    public function update($id, array $data): array
    {
        $user = User::where('id', $id)->orWhere('uuid', $id)->firstOrFail();

        try {
            $validated = validator($data, [
                'username' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'password' => 'sometimes|string|min:6',
                'role' => 'sometimes|string|in:admin,moderator,member',
                'date_of_birth' => 'nullable|date',
                'approval_status' => 'sometimes|string|in:pending,approved,rejected,banned',
            ])->validate();

            $updateData = [];
            if (isset($validated['username'])) {
                $updateData['username'] = $validated['username'];
            }
            if (isset($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }
            if (isset($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }
            if (isset($validated['role'])) {
                $updateData['role'] = $validated['role'];
            }
            if (isset($validated['date_of_birth'])) {
                $updateData['date_of_birth'] = $validated['date_of_birth'];
            }
            if (isset($validated['approval_status'])) {
                $updateData['approval_status'] = $validated['approval_status'];
                if ($validated['approval_status'] === 'approved') {
                    $updateData['approved_at'] = now();
                    $updateData['rejected_at'] = null;
                    $updateData['banned_at'] = null;
                } elseif ($validated['approval_status'] === 'rejected') {
                    $updateData['rejected_at'] = now();
                    $updateData['approved_at'] = null;
                    $updateData['banned_at'] = null;
                } elseif ($validated['approval_status'] === 'banned') {
                    $updateData['banned_at'] = now();
                    $updateData['approved_at'] = null;
                    $updateData['rejected_at'] = null;
                    $user->tokens()->delete();
                }
            }

            $user->update($updateData);

            return [
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->only(['uuid', 'member_id', 'username', 'email', 'role', 'approval_status', 'created_at', 'approved_at', 'rejected_at', 'banned_at']),
                'errors' => [],
                'meta' => ['timestamp' => now()->toDateTimeString()]
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors(),
                'meta' => ['timestamp' => now()->toDateTimeString()]
            ];
        }
    }

    public function delete($id): void
    {
        $user = User::where('id', $id)->orWhere('uuid', $id)->firstOrFail();
        $user->delete();
    }

    public function banUser($id): void
    {
        $user = User::where('id', $id)->orWhere('uuid', $id)->firstOrFail();
        if ($user->approval_status === 'banned') {
            throw ValidationException::withMessages([
                'user' => ['User is already banned']
            ])->status(400);
        }

        $user->update([
            'approval_status' => 'banned',
            'banned_at' => now(),
            'approved_at' => null,
            'rejected_at' => null,
        ]);

        // Revoke all tokens for the user
        $user->tokens()->delete();
    }
}
