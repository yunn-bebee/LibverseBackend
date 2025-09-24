<?php

namespace Modules\User\App\Services;

use App\Mail\LibiverseEmail;
use App\Models\Book;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\Forum;
use App\Models\Post;
use App\Models\ReadingChallenge;
use App\Models\Thread;
use App\Models\User;
use App\Models\UserChallengeBook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Notification\App\Notifications\GenericNotification;
use Modules\Notification\App\Notifications\UserFollowedNotification;
use Modules\User\App\Contracts\UserServiceInterface;

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
            ->with('profile');

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


            $updateData = $this->prepareUpdateData($data, $user);
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

        if ($user->approval_status == 'banned') {
            throw ValidationException::withMessages([
                'user' => ['User is already banned']
            ])->status(400);
        }

        $user->update([
            'approval_status' => 'banned',

        ]);

        $user->tokens()->delete();
        return true;
    }

    public function followUser(User $follower, User $followee): void
    {
        if ($follower->id === $followee->id) {
            throw new \Exception('Cannot follow yourself', 400);
        }

        $follower->following()->syncWithoutDetaching([$followee->id]);
        new GenericNotification(User::find($follower->id) ,"You got a new follower {$follower->username}" , "",  "" , "Check out Profile" );
    }

    public function unfollowUser(User $follower, User $followee): void
    {
        $follower->following()->detach($followee->id);
    }

    public function getFollowers(User $user, int $perPage = 15)
    {
        return $user->followers()->paginate($perPage);
    }

    public function getFollowing(User $user, int $perPage = 15)
    {
        return $user->following()->paginate($perPage);
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
        ];

        $now = now();

        switch ($status) {
            case 'approved':
                $data['approved_at'] = $now;
                break;
            case 'rejected':
                $data['rejected_at'] = $now;
                break;
        }

        return $data;
    }
        public function disableUser($id): bool
    {
        $user = User::where('id', $id)->orWhere('uuid', $id)->firstOrFail();

        if ($user->is_disabled) {
            throw ValidationException::withMessages([
                'user' => ['User is already disabled']
            ])->status(400);
        }

        $user->update([
            'approval_status' => 'banned',
            'is_disabled' => true,
            'disabled_at' => now(),
        ]);

        $user->tokens()->delete();
        return true;
    }
    public function getStats($id)
    {
        $user = User::where('id', $id)->orWhere('uuid', $id)->firstOrFail();

        return [
            'books_read' => $user->challengeBooks()->where('status', 'completed')->count(),
            'badges_earned' => $user->badges()->count(),
            'threads_created' => $user->threads()->count(),
            'posts_created' => $user->posts()->count(),
            'comments_created' => $user->comments()->count(),
        ];
    }
    public function enableUser($id): bool
    {
        $user = User::where('id', $id)->orWhere('uuid', $id)->firstOrFail();

        if (!$user->is_disabled) {
            throw ValidationException::withMessages([
                'user' => ['User is not disabled']
            ])->status(400);
        }

        $user->update([
            'is_disabled' => false,
            'disabled_at' => null,
            'approval_status' => 'approved',
        ]);

        return true;
    }
    public function adminStats(): array{
        $stats = [
        'users' => [
            'total' => User::count(),
            'by_role' => [
                'admin' => User::where('role', 'Admin')->count(),
                'moderator' => User::where('role', 'Moderator')->count(),
                'member' => User::where('role', 'Member')->count(),
            ],
            'by_status' => [
                'pending' => User::where('approval_status', 'pending')->count(),
                'approved' => User::where('approval_status', 'approved')->count(),
                'rejected' => User::where('approval_status', 'rejected')->count(),
                'banned' => User::where('approval_status', 'banned')->count(),
            ],
            // 'active_last_30_days' => User::where('last_active', '>=', now()->subDays(30))->count(),
        ],
        'books' => [
            'total' => Book::count(),

            'added_last_30_days' => Book::where('created_at', '>=', now()->subDays(30))->count(),
        ],
        'forums' => [
            'total' => Forum::count(),
            'public' => Forum::where('is_public', true)->count(),
            'private' => Forum::where('is_public', false)->count(),
            'active_threads' => Thread::where('created_at', '>=', now()->subDays(30))->count(),
        ],
        'threads' => [
            'total' => Thread::count(),
            'locked' => Thread::where('is_locked', true)->count(),
        ],
        'posts' => [
            'total' => Post::count(),
            'flagged' => Post::where('is_flagged', true)->count(),
        ],
        'events' => [
            'total' => Event::count(),
            'upcoming' => Event::where('start_time', '>=', now())->count(),
            'total_rsvps' => EventRsvp::count(),
            'avg_rsvps_per_event' => Event::whereHas('rsvps')->count() ? EventRsvp::count() / Event::whereHas('rsvps')->count() : 0,
        ],
        'challenges' => [
            'total' => ReadingChallenge::count(),
            'active' => ReadingChallenge::where('is_active', true)->count(),
            'total_participants' => UserChallengeBook::distinct('user_id')->count(),
            'completions' => UserChallengeBook::where('status', 'completed')->count(),
        ],
    ];
    return $stats;
    }
    public function warnUser($uuid , $request)
    {

    $request->validate(['message' => 'required|string|max:255']);

    $user = User::where('uuid', $uuid)->firstOrFail();

    send_notification(
        $user,
        'Warning',
        "You have been warned: {$request->message}",
        url('/dashboard'),
        'View Dashboard'
    );

    if ($user->email_notifications) {
        Mail::to($user->email)->send(new LibiverseEmail(
            title: 'Warning from Libiverse',
            content: $request->message,
            actionUrl: url('/dashboard'),
            actionText: 'View Dashboard'
        ));
        return true;
    }

}    public function me(): User
    {
        $userId = Auth::id();
        $user = User::with('profile')->find($userId);
        if (!$user) {
            abort(401, 'Unauthenticated');
        }
        return $user;
    }
}
