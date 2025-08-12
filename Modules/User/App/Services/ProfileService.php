<?php

namespace Modules\User\App\Services;

use App\Models\User;
use Modules\User\App\Contracts\ProfileServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService implements ProfileServiceInterface
{
    public function getUserProfile(User $user)
    {
        return $user->load('profile');
    }

    public function updateUserProfile(User $user, array $data)
    {
        try {
            // Update user fields
            $user->update([
                'username' => $data['username'] ?? $user->username,
                'email' => $data['email'] ?? $user->email,
            ]);

            // Handle profile picture upload
            $profilePicture = $this->handleProfilePicture($user, $data['profile_picture'] ?? null);

            // Update or create profile
            $profileData = [
                'bio' => $data['bio'] ?? null,
                'profile_picture' => $profilePicture,
                'website' => $data['website'] ?? null,
                'location' => $data['location'] ?? null,
                'reading_preferences' => $data['reading_preferences'] ?? null,
            ];

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );

            return $user->load('profile');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteUserProfile(User $user, string $password)
    {
        if (!Hash::check($password, $user->password)) {
            throw new \Exception('Invalid password', 403);
        }

        // Delete profile picture if exists
        if ($user->profile && $user->profile->profile_picture) {
            Storage::delete($user->profile->profile_picture);
        }

        // Delete profile and user
        $user->profile()->delete();
        $user->delete();

        return true;
    }

    private function   handleProfilePicture(User $user, $profilePicture = null)
    {
        if (!$profilePicture) {
            return $user->profile->profile_picture ?? null;
        }

        // Delete old picture if exists
        if ($user->profile && $user->profile->profile_picture) {
            Storage::delete($user->profile->profile_picture);
        }

        // Store new picture
        return $profilePicture->store('profile-pictures');
    }
}
