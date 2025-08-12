<?php

namespace Modules\User\App\Contracts;

use App\Models\User;

interface UserServiceInterface
{
    public function get($id): ?User;
    public function getAll(): array;
    public function save(array $data): array;
    public function update($id, array $data): array;
    public function delete($id): void;
    public function banUser($id): void;
}
