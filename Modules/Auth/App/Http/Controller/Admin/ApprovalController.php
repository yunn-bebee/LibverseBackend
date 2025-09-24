<?php

namespace Modules\Auth\App\Http\Controller\Admin;

use App\Mail\LibiverseEmail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
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
        if (!$result) {
            return apiResponse(
                false,
                'User approval failed',
                null,
                500
            );
        }
          Mail::to($user->email)->send(new LibiverseEmail(
        'Your Libiverse Account is Approved',
        'Congratulations! Your Libiverse account has been approved. You can now log in and enjoy all the features.',
        url('/login'), // action URL
        'Login Now'    // action button text
    ));
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
