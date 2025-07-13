<?php

namespace Modules\User\App\Services;

use App\Models\User;
use Modules\User\App\Contracts\ProfileServiceInterface;
use Illuminate\Support\Facades\Hash;
class ProfileService implements ProfileServiceInterface
{
    public function getUserProfile(User $user)
    {
        return $user->load('profile');
    }

    public function updateUserProfile(User $user, array $data)
    {
        // Update user fields
        $user->update([
            'username' => $data['username'] ?? $user->username,
            'email' => $data['email'] ?? $user->email,
        ]);

        // Update or create profile
        $profileData = [
            'bio' => $data['bio'] ?? null,
            'location' => $data['location'] ?? null,
            'website' => $data['website'] ?? null,
        ];
        
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return $user->load('profile');
    }

   public function deleteUserProfile(User $user, string $password)
{
    // Verify password before deletion
    if (!Hash::check($password, $user->password)) {
        abort(403, 'Invalid password');
    }

    // Delete profile using your existing relationship
    $user->userprofile()->delete();
    
    // Delete user account
    $user->delete();
    
    return true;
}

}