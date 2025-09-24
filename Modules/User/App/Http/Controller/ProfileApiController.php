<?php

namespace Modules\User\App\Http\Controller;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\User\App\Resources\UserProfileApiResource;
use Modules\User\App\Services\ProfileService;

class ProfileApiController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function show(int $id)
    {
        try {
            $profile = $this->profileService->getUserProfile($id);
            return apiResponse(true, 'Profile retrieved successfully',  new UserProfileApiResource($profile));
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

      public function update(Request $request)
   {
       $user = Auth::user();
       try {
           // Log request data for debugging
        

           $validated = $request->validate([
               'username' => 'sometimes|string|max:255',
               'email' => 'sometimes|email|unique:users,email,' . $user->id,
               'bio' => 'nullable|string',
               'website' => 'nullable|url',
               'location' => 'nullable|string',
               'profile_picture' => 'nullable|image|mimes:jpeg,png,gif|max:2048',
           ]);

           $profile = $this->profileService->updateUserProfile($user, $validated);
           return apiResponse(true, 'Profile updated successfully', new UserProfileApiResource($profile));
       } catch (\Illuminate\Validation\ValidationException $e) {
           Log::warning('Validation error updating profile', [
               'user_id' => $user->id,
               'errors' => $e->errors(),
           ]);
           return apiResponse(false, 'Validation failed: ' . implode(', ', array_merge(...array_values($e->errors()))), null, 422);
       } catch (\Exception $e) {
           Log::error('Error updating profile', [
               'user_id' => $user->id,
               'error' => $e->getMessage(),
               'trace' => $e->getTraceAsString(),
           ]);
           return apiResponse(false, $e->getMessage(), null, 422);
       }
   }

    public function destroy(Request $request, User $user)
    {
        try {
            $request->validate(['password' => 'required|string']);

            $this->profileService->deleteUserProfile($user, $request->password);
            return apiResponse(true, 'Profile deleted successfully');
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 400);
        }
    }
}
