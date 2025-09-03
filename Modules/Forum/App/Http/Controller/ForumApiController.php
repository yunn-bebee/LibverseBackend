<?php


namespace Modules\Forum\App\Http\Controller;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Forum\App\Contracts\ForumServiceInterface;
use Modules\Forum\App\Contracts\ThreadServiceInterface;
use Modules\Forum\App\Http\Requests\ForumRequest;
use Modules\Forum\App\Http\Requests\ThreadRequest;
use Modules\Forum\App\Resources\ForumApiResource;
use Modules\Forum\App\Resources\ThreadApiResource;
use Modules\User\App\Resources\UserApiResource;

class ForumApiController extends Controller
{
    protected $forumService;
    protected $threadService;

    public function __construct(ForumServiceInterface $forumService, ThreadServiceInterface $threadService)
    {
        $this->forumService = $forumService;
        $this->threadService = $threadService;
    }

    /**
     * List all forums (paginated, filter by category or is_public).
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->query();
        $perPage = $request->query('per_page', 20);
        $page = $request->query('page', 1);

        $forums = $this->forumService->getAll($filters, $perPage, $page);

        return apiResponse(
            true,
            'Forums retrieved successfully',
            ForumApiResource::collection($forums),
            200,
            [],
            $forums
        );
    }

    /**
     * Create a new forum.
     */
    public function store(ForumRequest $request): JsonResponse
{


    $forum = $this->forumService->create($request->validated());

    return apiResponse(
        true,
        'Forum created successfully',
        new ForumApiResource($forum),
        201
    );
}

    /**
     * Get a single forum with its threads.
     */
    public function show(Forum $forum): JsonResponse
    {
        $forum = $this->forumService->find($forum->id);

        return apiResponse(
            true,
            'Forum retrieved successfully',
            new ForumApiResource($forum)
        );
    }

    /**
     * Update a forum.
     */
    public function update(Forum $forum, ForumRequest $request): JsonResponse
    {
        $forum = $this->forumService->update($forum->id, $request->validated());

        return apiResponse(
            true,
            'Forum updated successfully',
            new ForumApiResource($forum)
        );
    }

    /**
     * Delete a forum (cascades to threads/posts).
     */
    public function destroy(Forum $forum): JsonResponse
    {
        $this->forumService->delete($forum->id);

        return apiResponse(
            true,
            'Forum deleted successfully',
            null,
            204
        );
    }

    /**
     * List threads in a forum (paginated, filter by post_type, is_pinned).
     */
    public function indexThreads(Forum $forum, Request $request): JsonResponse
    {
        $filters = $request->query();
        $perPage = $request->query('per_page', 20);

        $threads = $this->threadService->getByForum($forum, $filters, $perPage);

        return apiResponse(
            true,
            'Threads retrieved successfully',
            ThreadApiResource::collection($threads),
            200,
            [],
            $threads
        );
    }

    /**
     * Create a thread in a forum.
     */
    public function storeThread(Forum $forum, ThreadRequest $request): JsonResponse
    {
        $thread = $this->threadService->create($forum, $request->validated());

        return apiResponse(
            true,
            'Thread created successfully',
            new ThreadApiResource($thread),
            201
        );
    }

    public function showThread(Thread $thread): JsonResponse
    {
        $thread = $this->threadService->getById($thread->id);

        return apiResponse(
            true,
            'Thread retrieved successfully',
            new ThreadApiResource($thread)
        );
    }
    /**
     * Toggle is_public for a forum.
     */
    public function togglePublic(Forum $forum): JsonResponse
    {
        $forum = $this->forumService->togglePublic($forum);

        return apiResponse(
            true,
            'Forum public status toggled successfully',
            new ForumApiResource($forum)
        );
    }

    /**
     * Toggle is_pinned for a thread.
     */
    public function toggleThreadPin(Forum $forum, Thread $thread): JsonResponse
    {
        $thread = $this->threadService->togglePin($thread);

        return apiResponse(
            true,
            'Thread pin status toggled successfully',
            new ThreadApiResource($thread)
        );
    }

    /**
     * Toggle is_locked for a thread.
     */
    public function toggleThreadLock(Forum $forum, Thread $thread): JsonResponse
    {
        $thread = $this->threadService->toggleLock($thread);

        return apiResponse(
            true,
            'Thread lock status toggled successfully',
            new ThreadApiResource($thread)
        );
    }

    /**
     * Join a forum.
     */
    public function join(Forum $forum): JsonResponse
    {
        try {
            $this->forumService->joinForum(Auth::user(), $forum);
            $message = $forum->is_public ? 'Successfully joined forum' : 'Join request sent';
            return apiResponse(true, $message, null, 200);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 400);
        }
    }

    /**
     * Leave a forum.
     */
    public function leave(Forum $forum): JsonResponse
    {
        try {
            $this->forumService->leaveForum(Auth::user(), $forum);
            return apiResponse(true, 'Successfully left forum', null, 200);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 400);
        }
    }

    /**
     * List forum members.
     */
    public function members(Forum $forum): JsonResponse
    {
        try {
            $members = $this->forumService->getForumMembers($forum);
            return apiResponse(true, 'Forum members retrieved successfully', UserApiResource::collection($members));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    /**
     * Approve a join request for a private forum.
     */
    public function approveJoinRequest(Request $request, Forum $forum): JsonResponse
    {
        try {
            $this->authorizeCreatorOrAdmin($forum);
            $request->validate(['user_id' => 'required|exists:users,id']);
            $user = User::findOrFail($request->user_id);
            $this->forumService->approveJoinRequest($user, $forum);
            return apiResponse(true, 'Join request approved', null, 200);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 400);
        }
    }

    /**
     * Reject a join request for a private forum.
     */
    public function rejectJoinRequest(Request $request, Forum $forum): JsonResponse
    {
        try {
            $this->authorizeCreatorOrAdmin($forum);
            $request->validate(['user_id' => 'required|exists:users,id']);
            $user = User::findOrFail($request->user_id);
            $this->forumService->rejectJoinRequest($user, $forum);
            return apiResponse(true, 'Join request rejected', null, 200);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 400);
        }
    }

    /**
     * Fetch activity feed for followed users.
     */
    public function activityFeed(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 15);
            $threads = $this->forumService->getActivityFeed(Auth::user(), $perPage);
            return apiResponse(true, 'Activity feed retrieved successfully', ThreadApiResource::collection($threads));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    /**
     * Authorize forum creator or admin.
     */
    protected function authorizeCreatorOrAdmin(Forum $forum): void
    {
        $user = Auth::user();
        if (!$user || ($forum->created_by !== $user->id && !$user->hasRole('admin'))) {
            throw new \Exception('Unauthorized to perform this action', 403);
        }
    }
}
