<?php

namespace Modules\Event\App\Http\Controller;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\Event\App\Contracts\EventServiceInterface;
use Modules\Event\App\Http\Requests\EventRequest;
use Modules\Event\App\Resources\EventApiResource;

class EventApiController extends Controller
{
    public function __construct(
        protected EventServiceInterface $EventService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $items = $this->EventService->getAll();
        return response()->json(EventApiResource::collection($items));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->EventService->create($data);
        return response()->json(new EventApiResource($item), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $item = $this->EventService->find($id);
        return response()->json(new EventApiResource($item));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $item = $this->EventService->update($id, $data);
        return response()->json(new EventApiResource($item));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->EventService->delete($id);
        return response()->json(['message' => 'Event deleted successfully']);
    }
}