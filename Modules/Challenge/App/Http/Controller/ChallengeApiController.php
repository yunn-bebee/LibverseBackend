<?php

namespace Modules\Challenge\App\Http\Controller;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\Challenge\App\Contracts\ChallengeServiceInterface;
use Modules\Challenge\App\Http\Requests\ChallengeRequest;
use Modules\Challenge\App\Resources\ChallengeApiResource;

class ChallengeApiController extends Controller
{
    public function __construct(
        protected ChallengeServiceInterface $ChallengeService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $items = $this->ChallengeService->getAll();
        return response()->json(ChallengeApiResource::collection($items));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ChallengeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->ChallengeService->create($data);
        return response()->json(new ChallengeApiResource($item), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $item = $this->ChallengeService->find($id);
        return response()->json(new ChallengeApiResource($item));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChallengeRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $item = $this->ChallengeService->update($id, $data);
        return response()->json(new ChallengeApiResource($item));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->ChallengeService->delete($id);
        return response()->json(['message' => 'Challenge deleted successfully']);
    }
}