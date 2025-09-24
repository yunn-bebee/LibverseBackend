<?php

namespace Modules\User\App\Contracts;

use App\Models\User;


interface ProfileServiceInterface
{
    public function getUserProfile(int $id);
    public function updateUserProfile(User $user, array $data);
    public function deleteUserProfile(User $user, string $password);
}
