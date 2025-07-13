<?php

namespace Modules\User\App\Http\Controller;

use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Modules\User\App\Contracts\ProfileServiceInterface;
use Modules\User\App\Resources\UserApiResource;
use Illuminate\Routing\Controller;
use Modules\User\App\Http\Requests\UpdateProfileRequest;

class ProfileApiController extends Controller
{
    protected $profileService;

    public function __construct(ProfileServiceInterface $profileService)
    {
        $this->profileService = $profileService;
    }

    public function show(Request $request)
    {
        $user = $this->profileService->getUserProfile($request->user());
        return apiResponse(true, 'Profile retrieved', new UserApiResource($user));
    }
/**
 * @param \App\Http\Requests\UpdateProfileRequest $request
 */
    public function update(UpdateProfileRequest $request)
{
    $user = $request->user(); // or Auth::user(), both are fine if middleware is applied

    $updatedUser = $this->profileService->updateUserProfile($user, $request->validated());

    return apiResponse(true, 'Profile updated', new UserApiResource($updatedUser));
}

    public function destroy(Request $request)
    {
        $this->profileService->deleteUserProfile($request->user() ,$request->input('password')  );
        return apiResponse(true, 'User profile and account deleted successfully');
    }
}