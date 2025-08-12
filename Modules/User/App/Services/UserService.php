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

    public function getAll(array $filters = [], bool $paginate = true, int $perPage = 20)
    {
        $query = User::query()
            ->select([
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
            ]);

        // Apply filters
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('username', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('member_id', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['status'])) {
            $query->where('approval_status', $filters['status']);
        }

        return $paginate
            ? $query->paginate($perPage)
            : $query->get();
    }

    public function save(array $data): array
    {
        try {
            $validated = validator($data, [
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
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
                'user' => $user,
                'message' => 'User created successfully. Pending approval.'
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ];
        }
    }

    public function update($id, array $data): array
    {
        $user = User::where('id', $id)->orWhere('uuid', $id)->firstOrFail();

        try {
            $validated = validator($data, [
                'username' => 'sometimes|string|max:255|unique:users,username,'.$user->id,
                'email' => 'sometimes|email|unique:users,email,'.$user->id,
                'password' => 'sometimes|string|min:8',
                'role' => 'sometimes|string|in:admin,moderator,member',
                'date_of_birth' => 'nullable|date',
                'approval_status' => 'sometimes|string|in:pending,approved,rejected,banned',
            ])->validate();

            $updateData = $this->prepareUpdateData($validated, $user);
            $user->update($updateData);

            return [
                'success' => true,
                'user' => $user,
                'message' => 'User updated successfully'
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ];
        }
    }

    public function delete($id): bool
    {
        $user = User::where('id', $id)->orWhere('uuid', $id)->firstOrFail();
        return $user->delete();
    }

    public function banUser($id): bool
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

        $user->tokens()->delete();
        return true;
    }

    private function prepareUpdateData(array $validated, User $user): array
    {
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
            $updateData = array_merge($updateData, $this->handleApprovalStatus($validated['approval_status']));
        }

        return $updateData;
    }

    private function handleApprovalStatus(string $status): array
    {
        $data = [
            'approval_status' => $status,
            'approved_at' => null,
            'rejected_at' => null,
            'banned_at' => null,
        ];

        $now = now();

        switch ($status) {
            case 'approved':
                $data['approved_at'] = $now;
                break;
            case 'rejected':
                $data['rejected_at'] = $now;
                break;
            case 'banned':
                $data['banned_at'] = $now;
                break;
        }

        return $data;
    }
}
