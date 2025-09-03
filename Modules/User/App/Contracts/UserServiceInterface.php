<?php

namespace Modules\User\App\Contracts;

use App\Models\User;

interface UserServiceInterface
{
    public function get($id): ?User;
    public function getAll(array $filters = [], bool $paginate = true, int $perPage = 20);
    public function save(array $data): array;
    public function update($id, array $data): array;
    public function delete($id): bool;
    public function banUser($id): bool;
     public function followUser(User $follower, User $followee): void;

    public function unfollowUser(User $follower, User $followee): void;

    public function getFollowers(User $user, int $perPage = 15);

    public function getFollowing(User $user, int $perPage = 15);

    public function disableUser($id): bool;
    public function getStats($id);

 }
