<?php

namespace Modules\User\App\Http\Controller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            return errorResponse($e->getMessage(), [], 500);
        }
    }

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
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 400);
        }
    }

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

    public function ban(User $user): JsonResponse
    {
        try {
            $this->userService->banUser($user->id);

            return apiResponse(
                true,
                'User banned successfully ' . $user->id,
                null,
                200
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function follow(User $user): JsonResponse
    {
        try {
            $this->userService->followUser(Auth::user(), $user);
            return apiResponse(true, 'Successfully followed user', null, 200);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 400);
        }
    }

    public function unfollow(User $user): JsonResponse
    {
        try {
            $this->userService->unfollowUser(Auth::user(), $user);
            return apiResponse(true, 'Successfully unfollowed user', null, 200);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 400);
        }
    }

    public function followers(User $user): JsonResponse
    {
        try {
            $followers = $this->userService->getFollowers($user);
            return apiResponse(true, 'Followers retrieved successfully', UserApiResource::collection($followers));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function following(User $user): JsonResponse
    {
        try {
            $following = $this->userService->getFollowing($user);
            return apiResponse(true, 'Following retrieved successfully', UserApiResource::collection($following));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }
       public function disable(User $user): JsonResponse
    {
        try {
            $this->userService->disableUser($user->id);
            return apiResponse(true, 'User disabled successfully  '. $user->id, null, 200);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 400);
        }
    }
       public function updateRole(Request $request, User $user): JsonResponse
    {
        try {
            $validated = $request->validate([
                'role' => 'required|string|in:admin,moderator,member',
            ]);

            $result = $this->userService->update($user->id, $validated);

            if (!$result['success']) {
                return errorResponse($result['message'], $result['errors'], 400);
            }

            return apiResponse(true, 'User role updated successfully', new UserApiResource($result['user']));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 400);
        }
    }
      public function stats(User $user): JsonResponse
    {
        try {
            $stats = $this->userService->getStats($user->id);
            return apiResponse(true, 'User stats retrieved successfully', $stats);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }
    public function enable(User $user): JsonResponse
    {
        try {
            $this->userService->enableUser($user->id);
            return apiResponse(true, 'User enabled successfully  '. $user->id, null, 200);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 400);
        }
    }
    public function current(): JsonResponse
    {
        try {
            $user = Auth::user();
            return apiResponse(true, 'Current user retrieved successfully', new UserApiResource($user));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }
    public function adminStats(): JsonResponse
    {
        try {
            $stats = $this->userService->adminStats();
            return apiResponse(true, 'Admin stats retrieved successfully', $stats);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), [],  500);
        }
    }
    public function warn($uuid, Request $request)
{
    $this->userService->warnUser($uuid, $request);

    return apiResponse(
        success: true,
        message: 'User warned successfully'
    );
}
}
