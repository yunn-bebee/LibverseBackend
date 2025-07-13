<?php

namespace Modules\Auth\App\Http\Controller\Admin;
use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Modules\Auth\App\Contracts\AuthServiceInterface;

class ApprovalController extends Controller
{
    public function __construct(
        protected AuthServiceInterface $authService
    ) {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    public function pendingUsers(): JsonResponse
    {
        $users = $this->authService->getPendingUsers();
        return apiResponse(
            true,
            'Pending users retrieved',
            $users
        );
    }

    public function approveUser(User $user): JsonResponse
    {
        $result = $this->authService->approveUser($user);
        return apiResponse(
            true,
            'User approved successfully',
            $result
        );
    }

    public function rejectUser(User $user): JsonResponse
    {
        $this->authService->rejectUser($user);
        return apiResponse(
            true,
            'User rejected successfully'
        );
    }
}