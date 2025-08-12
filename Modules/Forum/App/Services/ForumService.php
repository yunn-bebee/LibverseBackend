<?php namespace Modules\Forum\App\Services;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Forum\App\Contracts\ForumServiceInterface;

class ForumService implements ForumServiceInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Forum::with(['creator', 'book'])->withCount('threads');

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Forum
    {
        return Forum::with(['createdBy', 'book', 'threads'])->findOrFail($id);
    }

    public function create(array $data): Forum
    {
        $data['created_by'] = Auth::id();
        return Forum::create($data);
    }

    public function update(int $id, array $data): Forum
    {
        $forum = Forum::findOrFail($id);
        $forum->update($data);
        return $forum;
    }

    public function delete(int $id): bool
    {
        $forum = Forum::findOrFail($id);
        return $forum->delete();
    }

    public function getThreads(Forum $forum, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Thread::where('forum_id', $forum->id)
            ->with(['user', 'book'])
            ->withCount('posts');

        if (isset($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function createThread(Forum $forum, array $data): Thread
    {
        $thread = new Thread($data);
        $thread->forum_id = $forum->id;
        $thread->user_id = Auth::id();
        $thread->save();
        return $thread;
    }
}
