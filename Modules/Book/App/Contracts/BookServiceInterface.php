<?php

namespace Modules\Book\App\Contracts;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookServiceInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function find(string $uuid): ?Book;
    public function create(array $data): Book;
    public function update(string $uuid, array $data): Book;
    public function delete(string $uuid): bool;
}