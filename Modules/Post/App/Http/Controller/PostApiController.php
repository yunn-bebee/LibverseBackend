<?php

namespace Modules\Post\App\Http\Controller;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\Post\App\Contracts\PostServiceInterface;
use Modules\Post\App\Http\Requests\PostRequest;
use Modules\Post\App\Resources\PostApiResource;

class PostApiController extends Controller
{
    public function __construct(
        protected PostServiceInterface $PostService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $items = $this->PostService->getAll();
        return response()->json(PostApiResource::collection($items));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->PostService->create($data);
        return response()->json(new PostApiResource($item), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $item = $this->PostService->find($id);
        return response()->json(new PostApiResource($item));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $item = $this->PostService->update($id, $data);
        return response()->json(new PostApiResource($item));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->PostService->delete($id);
        return response()->json(['message' => 'Post deleted successfully']);
    }
}