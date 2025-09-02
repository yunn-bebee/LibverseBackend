<?php

namespace Modules\Event\App\Contracts;

use App\Models\Event;
use Illuminate\Pagination\LengthAwarePaginator;

interface EventServiceInterface
{
    public function getAll(array $filters = [], int $perPage = 10, int $page = 1): LengthAwarePaginator;

    public function find(int $id): ?Event;

    public function create(array $data): Event;

    public function update(int $id, array $data): Event;

    public function delete(int $id): bool;

    public function rsvp(int $eventId, int $userId, string $status): bool;

    public function getRsvpCounts(int $eventId): array;
}
