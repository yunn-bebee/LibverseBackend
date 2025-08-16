<?php

namespace Modules\Post\App\Contracts;

use App\Models\Post;
use App\Models\Thread;
use Illuminate\Pagination\LengthAwarePaginator;


interface PostServiceInterface
{
    public function create(Thread $thread, array $data): Post;
    public function getByThread(Thread $thread, array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function find(string $id): ?Post;
    public function update(Post $post, array $data): Post;
    public function delete(Post $post): bool;
}
