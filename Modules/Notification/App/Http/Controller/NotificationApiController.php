<?php

namespace Modules\Notification\App\Http\Controller;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\Notification\App\Contracts\NotificationServiceInterface;
use Modules\Notification\App\Http\Requests\NotificationRequest;
use Modules\Notification\App\Resources\NotificationApiResource;

class NotificationApiController extends Controller
{
    public function __construct(
        protected NotificationServiceInterface $NotificationService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $items = $this->NotificationService->getAll();
        return response()->json(NotificationApiResource::collection($items));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NotificationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->NotificationService->create($data);
        return response()->json(new NotificationApiResource($item), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $item = $this->NotificationService->find($id);
        return response()->json(new NotificationApiResource($item));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NotificationRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $item = $this->NotificationService->update($id, $data);
        return response()->json(new NotificationApiResource($item));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->NotificationService->delete($id);
        return response()->json(['message' => 'Notification deleted successfully']);
    }
}