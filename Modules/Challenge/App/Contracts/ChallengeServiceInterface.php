<?php

namespace Modules\Challenge\App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\ReadingChallenge;

interface ChallengeServiceInterface
{
    public function getAll(array $filters = [], int $perPage = 10, int $page = 1): LengthAwarePaginator;
    public function find(int $id): ?ReadingChallenge;
    public function create(array $data): ReadingChallenge;
    public function update(int $id, array $data): ReadingChallenge;
    public function delete(int $id): bool;
    public function joinChallenge(int $challengeId, int $userId): array;
    public function addBookToChallenge(int $challengeId, int $userId, int $bookId, string $status): bool;
    public function updateBookStatus(int $recordId, string $status, int $rating = null, string $review = null): bool;
    public function getUserProgress(int $userId, int $challengeId): array;
    public function getLeaderboard(int $challengeId): array;
    public function hasUserJoined(int $userId, int $challengeId): bool;
}
