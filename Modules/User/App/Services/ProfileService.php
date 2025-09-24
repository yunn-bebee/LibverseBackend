<?php

namespace Modules\User\App\Services;

use App\Models\User;
use App\Models\UserProfile;
use Carbon\Traits\ToStringFormat;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\User\App\Contracts\ProfileServiceInterface;
use PHPUnit\Util\ThrowableToStringMapper;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProfileService implements ProfileServiceInterface
{
    public function getUserProfile(int $id)
    {
        return UserProfile::where('user_id', $id)->first();
    }

   public function updateUserProfile(User $user, array $data)
{
    try {
        // Start a database transaction
        DB::beginTransaction();

        // Update user fields
        $user->update([
            'username' => $data['username'] ?? $user->username,
            'email' => $data['email'] ?? $user->email,
        ]);

        // Handle profile picture upload
        $profilePicture = $this->handleProfilePicture($user, $data['profile_picture'] ?? null);

        // Prepare profile data
        $profileData = [
            'bio' => $data['bio'] ?? null,
            'profile_picture' => $profilePicture,
            'website' => $data['website'] ?? null,
            'location' => $data['location'] ?? null,
        ];

        // Update or create profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        // Commit the transaction
        DB::commit();

        // Load the profile relationship and return the user
        return $user->load('profile');

    } catch (QueryException $e) {
        // Rollback transaction on database error
        DB::rollBack();

        // Log the error with context
        Log::error('Database error updating user profile', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'data' => $data,
        ]);

        // Throw a custom exception with a user-friendly message
        throw new Exception('Failed to update profile due to a database error. Please try again. ' . $e->getMessage(), 500);

    } catch (FileException $e) {
        // Rollback transaction on file upload error
        DB::rollBack();

        // Log the error with context
        Log::error('File upload error in profile update', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        throw new Exception('Failed to upload profile picture. Please ensure the file is valid and try again.', 422);

    } catch (Exception $e) {
        // Rollback transaction on any other error
        DB::rollBack();

        // Log the error with context
        Log::error('Unexpected error updating user profile', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'data' => $data,
        ]);

        throw new Exception('An unexpected error occurred while updating your profile. Please try again.', 500);
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
private function handleProfilePicture(User $user, $file = null): string
{
    try {
        // Log file details for debugging
        if ($file) {
            Log::debug('Profile picture file received', [
                'user_id' => $user->id,
                'file_name' => $file instanceof UploadedFile ? $file->getClientOriginalName() : 'Not an UploadedFile',
                'file_size' => $file instanceof UploadedFile ? $file->getSize() : null,
                'file_type' => $file instanceof UploadedFile ? $file->getMimeType() : null,
            ]);
        }

        if (!$file instanceof UploadedFile || !$file->isValid()) {
            // Return existing picture or default if no profile exists
            return $user->profile && $user->profile->profile_picture
                ? $user->profile->profile_picture
                : 'profiles/default-avatar.png';
        }

        // Generate a unique file name
        $fileName = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Store the file
        $path = $file->storeAs('profiles', $fileName, 'public');

        // Delete old profile picture if it exists and isn't the default
        if ($user->profile && $user->profile->profile_picture && $user->profile->profile_picture !== 'profiles/default-avatar.png') {
            Storage::disk('public')->delete($user->profile->profile_picture);
        }

        return $path;

    } catch (FileException $e) {
        Log::error('FileException in handleProfilePicture', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        throw new FileException('Failed to process profile picture. Please ensure the file is valid.', 422);
    } catch (Exception $e) {
        Log::error('Unexpected error in handleProfilePicture', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        throw new FileException('Failed to process profile picture. Please ensure the file is valid.', 422);
    }
}
 }
