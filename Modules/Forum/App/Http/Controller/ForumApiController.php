<?php
namespace Modules\Forum\App\Http\Controller;

use App\Http\Controllers\Controller;
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
        return response()->json(ForumApiResource::collection($forums));
    }

    public function store(ForumRequest $request): JsonResponse
    {
        $forum = $this->forumService->create($request->validated());
        return response()->json(new ForumApiResource($forum), 201);
    }

    public function show(Forum $forum): JsonResponse
    {
        return response()->json(new ForumApiResource($forum));
    }

    public function update(Forum $forum, ForumRequest $request): JsonResponse
    {
        $forum = $this->forumService->update($forum->id, $request->validated());
        return response()->json(new ForumApiResource($forum));
    }

    public function destroy(Forum $forum): JsonResponse
    {
        $this->forumService->delete($forum->id);
        return response()->json(null, 204);
    }

    public function getThreads(Forum $forum, Request $request): JsonResponse
    {
        $filters = $request->query();
        $perPage = $request->query('per_page', 20);
        $threads = $this->forumService->getThreads($forum, $filters, $perPage);
        return response()->json(ThreadApiResource::collection($threads));
    }

    public function storeThread(Forum $forum, ThreadRequest $request): JsonResponse
    {
        $thread = $this->forumService->createThread($forum, $request->validated());
        return response()->json(new ThreadApiResource($thread), 201);
    }
}
