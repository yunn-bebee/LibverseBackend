<?php
namespace Modules\Forum\App\Contracts;

use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface ForumServiceInterface
{
    public function getAll(array $filters = [], int $perPage = 20 , int $page = 1): LengthAwarePaginator;
    public function find(int $id): ?Forum;
    public function create(array $data): Forum;
    public function update(int $id, array $data): Forum;
    public function delete(int $id): bool;
    public function getThreads(Forum $forum, array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function createThread(Forum $forum, array $data): Thread;

    public function togglePublic(Forum $forum): Forum;
     /**
     * Join a forum.
     *
     * @param User $user
     * @param Forum $forum
     * @return void
     */
    public function joinForum(User $user, Forum $forum): void;

    /**
     * Leave a forum.
     *
     * @param User $user
     * @param Forum $forum
     * @return void
     */
    public function leaveForum(User $user, Forum $forum): void;

    /**
     * List forum members.
     *
     * @param Forum $forum
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getForumMembers(Forum $forum, int $perPage = 15): LengthAwarePaginator;

    /**
     * Approve a join request for a private forum.
     *
     * @param User $user
     * @param Forum $forum
     * @return void
     */
    public function approveJoinRequest(User $user, Forum $forum): void;

    /**
     * Reject a join request for a private forum.
     *
     * @param User $user
     * @param Forum $forum
     * @return void
     */
    public function rejectJoinRequest(User $user, Forum $forum): void;

    /**
     * Fetch activity feed for followed users.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActivityFeed(array $filters = []): LengthAwarePaginator;
}

