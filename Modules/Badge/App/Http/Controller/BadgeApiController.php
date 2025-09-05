<?php

namespace Modules\Badge\App\Http\Controller;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\Badge\App\Contracts\BadgeServiceInterface;
use Modules\Badge\App\Http\Requests\BadgeRequest;
use Modules\Badge\App\Resources\BadgeApiResource;

class BadgeApiController extends Controller
{
    public function __construct(
        protected BadgeServiceInterface $BadgeService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $items = $this->BadgeService->getAll();
        return apiResponse(true, 'Badges retrieved successfully', $items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BadgeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->BadgeService->create($data);
        return apiResponse(true, 'Badge created successfully', new BadgeApiResource($item), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $item = $this->BadgeService->find($id);
        return apiResponse(true, 'Badge retrieved successfully', new BadgeApiResource($item));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BadgeRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $item = $this->BadgeService->update($id, $data);
        return apiResponse(true, 'Badge updated successfully', new BadgeApiResource($item));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->BadgeService->delete($id);
        return apiResponse(true, 'Badge deleted successfully');
    }
}
