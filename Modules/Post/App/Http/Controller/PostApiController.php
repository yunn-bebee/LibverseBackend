<?php

namespace Modules\Post\App\Http\Controller;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Post\App\Contracts\PostServiceInterface;
use Modules\Post\App\Http\Requests\PostRequest;
use Modules\Post\App\Http\Requests\PostActionRequest;
use Modules\Post\App\Http\Requests\MediaRequest;
use Modules\Post\App\Resources\PostApiResource;

class PostApiController extends Controller
{
    protected $postService;

    public function __construct(PostServiceInterface $postService)
    {
        $this->postService = $postService;
    }

   public function index(Thread $thread, Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 20);
            $filters = $request->only(['search', 'is_flagged']);
            $posts = $this->postService->getByThread($thread, $filters, $perPage);
            return apiResponse(
                success: true,
                message: 'Posts retrieved successfully',
                data: PostApiResource::collection($posts),
               
                errors: [],
                paginator: $posts // Pass the LengthAwarePaginator directly
            );
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function store(Thread $thread, PostRequest $request): JsonResponse
    {
        try {
            $post = $this->postService->create($thread, $request->validated());
            return apiResponse(true, 'Post created successfully', new PostApiResource($post), 201);
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 400);
        }
    }

    public function show(Post $post): JsonResponse
    {
        try {
            $post = $this->postService->find($post->uuid);
            if (!$post) {
                return apiResponse(false, 'Post not found', null, 404);
            }
            return apiResponse(true, 'Post retrieved successfully', new PostApiResource($post));
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function update(Post $post, PostRequest $request): JsonResponse
    {
        try {
            $post = $this->postService->update($post, $request->validated());
            return apiResponse(true, 'Post updated successfully', new PostApiResource($post));
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 400);
        }
    }

    public function destroy(Post $post): JsonResponse
    {
        try {
            $this->postService->delete($post);
            return apiResponse(true, 'Post deleted successfully', null, 204);
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function like(Post $post, PostActionRequest $request): JsonResponse
    {
        try {
            $action = $request->input('action', 'like'); // 'like' or 'unlike'
            $this->postService->toggleLike($post, $action === 'like');
            $message = $action === 'like' ? 'Post liked successfully' : 'Post unliked successfully';
            return apiResponse(true, $message, null, 200);
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 400);
        }
    }

    public function save(Post $post, PostActionRequest $request): JsonResponse
    {
        try {
            $action = $request->input('action', 'save'); // 'save' or 'unsave'
            $this->postService->toggleSave($post, $action === 'save');
            $message = $action === 'save' ? 'Post saved successfully' : 'Post unsaved successfully';
            return apiResponse(true, $message, null, 200);
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 400);
        }
    }

    public function comment(Post $post, PostRequest $request): JsonResponse
    {
        try {
            $comment = $this->postService->createComment($post, $request->validated());
            return apiResponse(true, 'Comment created successfully', new PostApiResource($comment), 201);
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 400);
        }
    }

    public function flag(Post $post): JsonResponse
    {
        try {
            $this->postService->toggleFlag($post);
            $message = $post->is_flagged ? 'Post flagged successfully' : 'Post unflagged successfully';
            return apiResponse(true, $message, new PostApiResource($post));
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 400);
        }
    }

    public function uploadMedia(Post $post, MediaRequest $request): JsonResponse
    {
        try {
            $media = $this->postService->uploadMedia($post, $request->validated());
            return apiResponse(true, 'Media uploaded successfully', ['media' => $media], 201);
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 400);
        }
    }
}
