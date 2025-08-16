<?php
namespace Modules\Forum\App\Contracts;

use App\Models\Forum;
use App\Models\Thread;
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
}
