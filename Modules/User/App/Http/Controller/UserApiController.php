<?php

namespace Modules\User\App\Http\Controller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\User\App\Http\Requests\StoreUserApiRequest;
use Modules\User\App\Http\Requests\UpdateUserApiRequest;
use Modules\User\App\Resources\UserApiResource;
use Modules\User\App\Services\UserService;

class UserApiController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * List all users (paginated, filter by search, role, or status).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $paginationParams = getPaginationParams($request);
            $filters = $request->only(['search', 'role', 'status']);

            $users = $this->userService->getAll(
                $filters,
                !$paginationParams['noPagination'],
                $paginationParams['perPage']
            );

            return apiResponse(
                true,
                'Users retrieved successfully',
                UserApiResource::collection($users),
                200,
                [],
                $users
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [],  500);
        }
    }

    /**
     * Get a single user.
     */
    public function show(User $user): JsonResponse
    {
        try {
            $user = $this->userService->get($user->id);

            if (!$user) {
                return errorResponse('User not found', [], 404);
            }

            return apiResponse(
                true,
                'User retrieved successfully',
                new UserApiResource($user)
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    /**
     * Create a new user.
     */
    public function store(StoreUserApiRequest $request): JsonResponse
    {
        try {
            $result = $this->userService->save($request->validated());

            if (!$result['success']) {
                return errorResponse(
                    $result['message'],
                    $result['errors'],
                    400
                );
            }

            return apiResponse(
                true,
                $result['message'],
                new UserApiResource($result['user']),
                201
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    /**
     * Update a user.
     */
    public function update(UpdateUserApiRequest $request, User $user): JsonResponse
    {
        try {
            $result = $this->userService->update($user->id, $request->validated());

            if (!$result['success']) {
                return errorResponse(
                    $result['message'],
                    $result['errors'],
                    400
                );
            }

            return apiResponse(
                true,
                $result['message'],
                new UserApiResource($result['user'])
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $this->userService->delete($user->id);

            return apiResponse(
                true,
                'User deleted successfully',
                null,
                204
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    /**
     * Ban a user.
     */
    public function ban(User $user): JsonResponse
    {
        try {
            $this->userService->banUser($user->id);

            return apiResponse(
                true,
                'User banned successfully',
                null,
                200
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }
}
