<?php

namespace Modules\Book\App\Contracts;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookServiceInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function find(int $id): ?Book;
    public function create(array $data): Book;
    public function update(int $id, array $data): Book;
    public function delete(int $id): bool;
    public function searchGoogleBooks(string $query, int $page = 1, int $perPage = 20): array;
    public function verify(int $id): Book;
}
