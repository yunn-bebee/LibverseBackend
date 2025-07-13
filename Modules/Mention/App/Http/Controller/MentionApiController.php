<?php

namespace Modules\Mention\App\Http\Controller;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\Mention\App\Contracts\MentionServiceInterface;
use Modules\Mention\App\Http\Requests\MentionRequest;
use Modules\Mention\App\Resources\MentionApiResource;

class MentionApiController extends Controller
{
    public function __construct(
        protected MentionServiceInterface $MentionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $items = $this->MentionService->getAll();
        return response()->json(MentionApiResource::collection($items));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MentionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->MentionService->create($data);
        return response()->json(new MentionApiResource($item), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $item = $this->MentionService->find($id);
        return response()->json(new MentionApiResource($item));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MentionRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $item = $this->MentionService->update($id, $data);
        return response()->json(new MentionApiResource($item));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->MentionService->delete($id);
        return response()->json(['message' => 'Mention deleted successfully']);
    }
}