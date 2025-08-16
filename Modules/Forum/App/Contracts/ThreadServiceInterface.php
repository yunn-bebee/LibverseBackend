<?php

namespace Modules\Forum\App\Contracts;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Pagination\LengthAwarePaginator;

interface ThreadServiceInterface
{
    public function create(Forum $forum, array $data): Thread;
    public function getByForum(Forum $forum, array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function getById(int $id): ?Thread;
    public function update(Thread $thread, array $data): Thread;
    public function delete(Thread $thread): bool;
    public function togglePin(Thread $thread): Thread;
    public function toggleLock(Thread $thread): Thread;
}
