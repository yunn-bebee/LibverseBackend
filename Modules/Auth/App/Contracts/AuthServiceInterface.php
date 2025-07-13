<?php

namespace Modules\Auth\App\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    public function register(array $data): array;
    public function login(array $credentials): array;
    public function logout(): void;
    public function getPendingUsers(): array;
    public function approveUser(User $user): array;
    public function rejectUser(User $user): void;
}