<?php
// app/Http/Controllers/Api/V1/Admin/ApprovalController.php
namespace App\Http\Controllers\API\V1\Admin;

use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    public function pendingUsers(): JsonResponse
    {
      

        $pendingUsers = User::where('approval_status', 'pending')
            ->select('uuid', 'member_id', 'email', 'username', 'created_at')
            ->get();

        return response()->json($pendingUsers);
    }

    public function approveUser(User $user): JsonResponse
    {
        // Authorization check
        if (!Auth::user()->role->isAdmin()) {
            return response()->json(['message' =>'Bro you no permission get out'], 403);
        }

        if ($user->approval_status !== 'pending') {
            return response()->json([
                'message' => 'User is not pending approval'
            ], 400);
        }

        $user->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'User approved successfully',
            'user' => [
                'uuid' => $user->uuid,
                'email' => $user->email,
                'approval_status' => $user->approval_status,
                'approved_at' => $user->approved_at
            ]
        ]);
    }

    public function rejectUser(User $user): JsonResponse
    {
        // Authorization check
        if (!Auth::user() ->role->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->approval_status !== 'pending') {
            return response()->json([
                'message' => 'User is not pending approval'
            ], 400);
        }

        $user->update([
            'approval_status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return response()->json([
            'message' => 'User rejected successfully',
            'user_id' => $user->uuid
        ]);
    }
}