<?php

namespace Modules\User\App\Http\Controller;

use App\Models\User;
use Modules\User\App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProfileApiController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function show(User $user)
    {
        try {
            $profile = $this->profileService->getUserProfile($user);
            return apiResponse(true, 'Profile retrieved successfully', $profile);
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'username' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,'.$user->id,
                'bio' => 'nullable|string',
                'website' => 'nullable|url',
                'location' => 'nullable|string',
                'reading_preferences' => 'nullable|json',
                'profile_picture' => 'nullable|image|max:2048',
            ]);

            $profile = $this->profileService->updateUserProfile($user, $validated);
            return apiResponse(true, 'Profile updated successfully', $profile);
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 400);
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
