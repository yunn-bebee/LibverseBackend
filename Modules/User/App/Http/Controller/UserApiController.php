<?php

namespace Modules\User\App\Http\Controller;

use Modules\User\App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;

class UserApiController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'role', 'status']);
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 20);
        $users = $this->userService->getAll();

        // Apply filtering if needed
        $filteredUsers = array_filter($users, function ($user) use ($filters) {
            if (isset($filters['search']) && $filters['search']) {
                $search = strtolower($filters['search']);
                return str_contains(strtolower($user['username']), $search) ||
                       str_contains(strtolower($user['email']), $search) ||
                       str_contains(strtolower($user['member_id']), $search);
            }
            if (isset($filters['role']) && $filters['role']) {
                return $user['role'] === $filters['role'];
            }
            if (isset($filters['status']) && $filters['status']) {
                return $user['approval_status'] === $filters['status'];
            }
            return true;
        });

        $total = count($filteredUsers);
        $paginatedUsers = array_slice($filteredUsers, ($page - 1) * $perPage, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => array_values($paginatedUsers),
            'meta' => [
                'total' => $total,
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'timestamp' => now()->toDateTimeString()
            ],
            'errors' => [],
        ]);
    }

    public function show($id): JsonResponse
    {
        $user = $this->userService->get($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'data' => null,
                'errors' => ['id' => ['User not found']],
                'meta' => ['timestamp' => now()->toDateTimeString()]
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => $user->only(['uuid', 'member_id', 'username', 'email', 'role', 'approval_status', 'created_at', 'approved_at', 'rejected_at', 'banned_at']),
            'errors' => [],
            'meta' => ['timestamp' => now()->toDateTimeString()]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $result = $this->userService->save($request->all());
        return response()->json($result, $result['success'] ? 201 : 400);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $result = $this->userService->update($id, $request->all());
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->userService->delete($id);
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
                'data' => null,
                'errors' => [],
                'meta' => ['timestamp' => now()->toDateTimeString()]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'data' => null,
                'errors' => ['id' => [$e->getMessage()]],
                'meta' => ['timestamp' => now()->toDateTimeString()]
            ], 400);
        }
    }

    public function ban($id): JsonResponse
    {
        try {
            $this->userService->banUser($id);
            return response()->json([
                'success' => true,
                'message' => 'User banned successfully',
                'data' => null,
                'errors' => [],
                'meta' => ['timestamp' => now()->toDateTimeString()]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to ban user',
                'data' => null,
                'errors' => $e->errors(),
                'meta' => ['timestamp' => now()->toDateTimeString()]
            ], $e->status);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to ban user',
                'data' => null,
                'errors' => ['id' => [$e->getMessage()]],
                'meta' => ['timestamp' => now()->toDateTimeString()]
            ], 400);
        }
    }
}
