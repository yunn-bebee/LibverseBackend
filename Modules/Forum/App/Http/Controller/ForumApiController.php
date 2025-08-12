<?php

namespace Modules\Forum\App\Http\Controller;
use App\Http\Controllers\Controller;
use App\Models\Forum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Forum\App\Contracts\ForumServiceInterface;
use Modules\Forum\App\Http\Requests\ForumRequest;
use Modules\Forum\App\Http\Requests\ThreadRequest;
use Modules\Forum\App\Resources\ForumApiResource;
use Modules\Forum\App\Resources\ThreadApiResource;

class ForumApiController extends Controller
{
    protected $forumService;

    public function __construct(ForumServiceInterface $forumService)
    {
        $this->forumService = $forumService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->query();
        $perPage = $request->query('per_page', 20);
        $forums = $this->forumService->getAll($filters, $perPage);

        return apiResponse(
            true,
            'Forums retrieved successfully',
            ForumApiResource::collection($forums)
        );
    }

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

    public function show(Forum $forum): JsonResponse
    {
        return apiResponse(
            true,
            'Forum retrieved successfully',
            new ForumApiResource($forum)
        );
    }

    public function update(Forum $forum, ForumRequest $request): JsonResponse
    {
        $forum = $this->forumService->update($forum->id, $request->validated());

        return apiResponse(
            true,
            'Forum updated successfully',
            new ForumApiResource($forum)
        );
    }

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

    public function getThreads(Forum $forum, Request $request): JsonResponse
    {
        $filters = $request->query();
        $perPage = $request->query('per_page', 20);
        $threads = $this->forumService->getThreads($forum, $filters, $perPage);

        return apiResponse(
            true,
            'Threads retrieved successfully',
            ThreadApiResource::collection($threads)
        );
    }

    public function storeThread(Forum $forum, ThreadRequest $request): JsonResponse
    {
        $thread = $this->forumService->createThread($forum, $request->validated());

        return apiResponse(
            true,
            'Thread created successfully',
            new ThreadApiResource($thread),
            201
        );
    }
}
