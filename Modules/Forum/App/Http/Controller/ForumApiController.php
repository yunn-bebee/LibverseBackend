<?php

namespace Modules\Forum\App\Http\Controller;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\Forum\App\Contracts\ForumServiceInterface;
use Modules\Forum\App\Http\Requests\ForumRequest;
use Modules\Forum\App\Resources\ForumApiResource;

class ForumApiController extends Controller
{
    public function __construct(
        protected ForumServiceInterface $ForumService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $items = $this->ForumService->getAll();
        return response()->json(ForumApiResource::collection($items));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ForumRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->ForumService->create($data);
        return response()->json(new ForumApiResource($item), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $item = $this->ForumService->find($id);
        return response()->json(new ForumApiResource($item));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ForumRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $item = $this->ForumService->update($id, $data);
        return response()->json(new ForumApiResource($item));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->ForumService->delete($id);
        return response()->json(['message' => 'Forum deleted successfully']);
    }
}