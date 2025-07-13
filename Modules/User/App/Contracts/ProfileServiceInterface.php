<?php

namespace Modules\User\App\Contracts;

use App\Models\User;


interface ProfileServiceInterface
{
    public function getUserProfile(User $user);
    public function updateUserProfile(User $user, array $data);
    public function deleteUserProfile(User $user, string $password);
}