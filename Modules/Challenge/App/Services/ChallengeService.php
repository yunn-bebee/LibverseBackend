<?php

namespace Modules\Challenge\App\Services;

use App\Models\Badge;
use App\Models\ChallengeBook;
use App\Models\ReadingChallenge;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserChallengeBook;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Challenge\App\Contracts\ChallengeServiceInterface;
use Modules\User\App\Resources\UserProfileApiResource;


class ChallengeService implements ChallengeServiceInterface
{
public function getAll(array $filters = [], int $perPage = 10, int $page = 1): LengthAwarePaginator
{
    $query = ReadingChallenge::with(['badge', 'books', 'creator', 'participants']);

    if (isset($filters['active'])) {
        $query->where('start_date', '<=', now('Asia/Tokyo'))
              ->where('end_date', '>=', now('Asia/Tokyo'));
    }

    if (isset($filters['current'])) {
        $query->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
    }

    // Filter by user's joined challenges
    if (isset($filters['is_joined']) && filter_var($filters['is_joined'], FILTER_VALIDATE_BOOLEAN)) {
        $userId = Auth::id();
        if (!$userId) {
            Log::warning('is_joined filter applied without authenticated user', ['filters' => $filters]);
            throw new Exception('is_joined filter requires an authenticated user', 403);
        }
        $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_challenge_books.user_id', $userId);
        });
    }

    try {
        $challenges = $query->orderBy('created_at', 'desc')
                           ->paginate($perPage, ['*'], 'page', $page);

        $userId = Auth::id();
        $challenges->getCollection()->transform(function ($challenge) use ($userId) {
            $challenge->has_joined = $this->hasUserJoined($userId, $challenge->id);
            if ($challenge->has_joined) {
                $challenge->progress = $this->getUserProgress($userId, $challenge->id);
            } else {
                $challenge->has_joined = false;
            }
            return $challenge;
        });

        Log::info('Challenges retrieved successfully', [
            'filters' => $filters,
            'user_id' => $userId ?? 'none',
            'total_results' => $challenges->total(),
        ]);

        return $challenges;
    } catch (\Exception $e) {
        Log::error('Error retrieving challenges', [
            'error' => $e->getMessage(),
            'filters' => $filters,
            'user_id' => $userId ?? 'none',
        ]);
        throw $e;
    }
}

    public function find(int $id): ?ReadingChallenge
    {
        $challenge = ReadingChallenge::with(['badge', 'creator'])->find($id);

        if (Auth::check() && $challenge) {
            $challenge->has_joined = $this->hasUserJoined(Auth::id(), $challenge->id);
            if ($challenge->has_joined) {
                $challenge->progress = $this->getUserProgress(Auth::id(), $challenge->id);
            }
        }

        return $challenge;
    }

    public function getBooks(int $challengeId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $challenge = ReadingChallenge::findOrFail($challengeId);
        return ChallengeBook::where('reading_challenge_id', $challengeId)
            ->with('book')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): ReadingChallenge
    {
        return DB::transaction(function () use ($data) {
            if (!isset($data['book_ids']) || !is_array($data['book_ids']) || empty($data['book_ids'])) {
                throw new \Exception('At least one book is required to create a challenge.');
            }

            $data['created_by'] = Auth::id();
            $challenge = ReadingChallenge::create($data);

            foreach ($data['book_ids'] as $bookId) {
                $this->addBookToChallenge($challenge->id, $bookId);
            }

            return $challenge->load('badge', 'creator', 'books');
        });
    }

    public function update(int $id, array $data): ReadingChallenge
    {
        return DB::transaction(function () use ($id, $data) {
            $challenge = ReadingChallenge::findOrFail($id);

            if (isset($data['book_ids']) && (empty($data['book_ids']) || !is_array($data['book_ids']))) {
                throw new \Exception('At least one book is required when updating challenge books.');
            }

            $challenge->update($data);

            if (isset($data['book_ids'])) {
                ChallengeBook::where('reading_challenge_id', $challenge->id)->delete();
                foreach ($data['book_ids'] as $bookId) {
                    $this->addBookToChallenge($challenge->id, $bookId);
                }
            }

            return $challenge->load('badge', 'creator', 'books');
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $challenge = ReadingChallenge::findOrFail($id);
            return $challenge->delete();
        });
    }

    public function joinChallenge(int $challengeId, int $userId): array
    {
        return DB::transaction(function () use ($challengeId, $userId) {
            if ($this->hasUserJoined($userId, $challengeId)) {
                throw new \Exception('You have already joined this challenge.');
            }

            $challenge = ReadingChallenge::with('books')->findOrFail($challengeId);

            if (!$challenge->is_active || $challenge->start_date > now() || $challenge->end_date < now()) {
                throw new \Exception('This challenge is not currently available to join.');
            }

            $firstBook = ChallengeBook::where('reading_challenge_id', $challengeId)
                                     ->orderBy('created_at', 'asc')
                                     ->first();

            if (!$firstBook) {
                throw new Exception('No books available in this challenge.');
            }

            $this->addUserBookToChallenge($challengeId, $userId, $firstBook->book_id, 'reading');

            return [
                'challenge' => $challenge,
                'message' => 'Successfully joined the challenge and started reading the first book!'
            ];
        });
    }

    public function addUserBookToChallenge(int $challengeId, int $userId, int $bookId, string $status, bool $isBook = false): bool
    {
        return DB::transaction(function () use ($challengeId, $userId, $bookId, $status, $isBook) {

            $challengeBook = ChallengeBook::where('reading_challenge_id', $challengeId)
                                         ->where('book_id', $bookId)
                                         ->first();

            if (!$challengeBook) {
                throw new \Exception('The selected book is not part of this challenge.' . $challengeId . $userId . $bookId );
            }

            $existingRecord = UserChallengeBook::where('user_id', $userId)
                                              ->where('challenge_id', $challengeId)
                                              ->where('book_id', $bookId)
                                              ->first();

            if ($existingRecord && !$isBook) {
                throw new \Exception('This book is already in your challenge reading list.');
            }
            if ($isBook && $existingRecord) {
                return false;
            }

            UserChallengeBook::create([
                'user_id' => $userId,
                'challenge_id' => $challengeId,
                'book_id' => $bookId,
                'status' => $status,
                'started_at' => $status === 'reading' ? now() : null,
            ]);

            return true;
        });
    }

    public function removeUserBookFromChallenge(int $challengeId, int $userId, int $bookId): bool
    {
        return DB::transaction(function () use ($challengeId, $userId, $bookId) {
            $user = Auth::user();
            if (!$user || !($user->hasRole('admin') || $user->hasRole('moderator'))) {
                throw new \Exception('Unauthorized to remove user book from challenge.');
            }

            $deleted = UserChallengeBook::where('challenge_id', $challengeId)
                                       ->where('user_id', $userId)
                                       ->where('book_id', $bookId)
                                       ->delete();
            return $deleted > 0;
        });
    }

    public function addBookToChallenge(int $challengeId, int $bookId): bool
    {
        return DB::transaction(function () use ($challengeId, $bookId) {
            $user = Auth::user();
            // if (!$user || !($user->hasRole('admin') || $user->hasRole('moderator'))) {
            //     throw new \Exception('Unauthorized to add book to challenge.');
            // }

            $challenge = ReadingChallenge::findOrFail($challengeId);

            $existingBook = ChallengeBook::where('reading_challenge_id', $challengeId)
                                        ->where('book_id', $bookId)
                                        ->first();

            if ($existingBook) {
                throw new \Exception('This book is already part of the challenge.');
            }

            ChallengeBook::create([
                'reading_challenge_id' => $challengeId,
                'book_id' => $bookId,
                'added_by' => Auth::id(),
            ]);

            return true;
        });
    }

    public function removeBookFromChallenge(int $challengeId, int $bookId): bool
    {
        return DB::transaction(function () use ($challengeId, $bookId) {
            $user = Auth::user();
            // if (!$user || !($user) || $user->hasRole('moderator'))) {
            //     throw new \Exception('Unauthorized to remove book from challenge.');
            // }

            $challenge = ReadingChallenge::findOrFail($challengeId);
            $deleted = ChallengeBook::where('reading_challenge_id', $challengeId)
                                   ->where('book_id', $bookId)
                                   ->delete();
            return $deleted > 0;
        });
    }

    public function updateBookStatus(int $recordId, string $status, ?int $rating = null, ?string $review = null): bool
    {
        return DB::transaction(function () use ($recordId, $status, $rating, $review) {
            $record = UserChallengeBook::where('user_id', Auth::id())
                ->where('book_id', $recordId)
                ->firstOrFail();
            $challengeId = $record->challenge_id;
            $userId = $record->user_id;

            $updates = ['status' => $status];

            if ($status === 'reading' && !$record->started_at) {
                $updates['started_at'] = now();
            }

            if ($status === 'completed' && !$record->completed_at) {
                $updates['completed_at'] = now();
                $this->assignNextBook($userId, $challengeId);
            }

            if ($rating !== null) {
                $updates['user_rating'] = $rating;
            }

            if ($review !== null) {
                $updates['review'] = $review;
            }

            return $record->update($updates);
        });
    }

    public function getUserProgress(int $userId, int $challengeId): array
    {
        if (!$this->hasUserJoined($userId, $challengeId)) {
            throw new \Exception('You have not joined this challenge yet.');
        }

        $challenge = ReadingChallenge::with('books')->findOrFail($challengeId);
        $completedBooks = UserChallengeBook::where('user_id', $userId)
            ->where('challenge_id', $challengeId)
            ->where('status', 'completed')
            ->count();
        $totalBooks = ChallengeBook::where('reading_challenge_id', $challengeId)->count();
        $isCompleted = $completedBooks >= $totalBooks;
        $books = UserChallengeBook::where('user_id', $userId)
            ->where('challenge_id', $challengeId)
            ->with('book')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->book->id,
                    'title' => $record->book->title,
                    'author' => $record->book->author,
                    'cover_image' => $record->book->cover_image,
                    'status' => $record->status,
                    'started_at' => $record->started_at,
                    'completed_at' => $record->completed_at,
                    'user_rating' => $record->user_rating,
                    'review' => $record->review,
                ];
            });
        $user = User::with('profile')->find($userId);
         if ($isCompleted && $challenge->badge_id) {
            $hasBadge = UserBadge::where('user_id', $userId)
                ->where('badge_id', $challenge->badge_id)
                ->where('challenge_id', $challengeId)
                ->exists();

            if (!$hasBadge) {
                UserBadge::create([
                    'user_id' => $userId,
                    'badge_id' => $challenge->badge_id,
                    'earned_at' => now(),
                    'challenge_id' => $challengeId,
                ]);
            }
        }
        return [
            'challenge_id' => $challengeId,

            'user' => $user,
            'challenge_name' => $challenge->name,
            'start_date' => $challenge->start_date,
            'end_date' => $challenge->end_date,
            'total_books' => $totalBooks,
            'books_read' => $completedBooks,
            'books' => $books,
            'books_remaining' => max(0, $totalBooks - $completedBooks),
            'percentage' => $totalBooks > 0 ? round(($completedBooks / $totalBooks) * 100, 2) : 0,
            'is_completed' => $isCompleted,
        ];
    }

    public function getLeaderboard(int $challengeId): array
    {
        $leaderboard = UserChallengeBook::select('user_id', DB::raw('COUNT(*) as books_read'))
            ->where('challenge_id', $challengeId)
            ->where('status', 'completed')
            ->groupBy('user_id')
            ->orderBy('books_read', 'desc')
            ->with(['user', 'user.profile'])
            ->get();
        return $leaderboard->map(function ($item) {
            return [
                'user' => [
                    'id' => $item->user->id,
                    'profile' => $item->user->profile ? new UserProfileApiResource($item->user->profile) : null,
                    'username' => $item->user->username,
                ],
                'books_read' => $item->books_read,
            ];
        })->toArray();
    }

    public function hasUserJoined(int $userId, int $challengeId): bool
    {
        return UserChallengeBook::where('user_id', $userId)
            ->where('challenge_id', $challengeId)
            ->exists();
    }

    public function getChallengeParticipantsProgress(int $challengeId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $challenge = ReadingChallenge::findOrFail($challengeId);

        $participants = $challenge->participants()->paginate($perPage, ['*'], 'page', $page);

        $participants->getCollection()->transform(function ($user) use ($challenge) {
            $completedBooksCount = UserChallengeBook::where('user_id', $user->id)
                ->where('challenge_id', $challenge->id)
                ->where('status', 'completed')
                ->count();

            $hasBadge = UserBadge::where('user_id', $user->id)
                ->where('badge_id', $challenge->badge_id)
                ->exists();

            $user->progress = [
                'books_completed' => $completedBooksCount,
                'target_count' => $challenge->target_count,
                'percentage' => $challenge->target_count > 0 ? round(($completedBooksCount / $challenge->target_count) * 100) : 0,
                'has_badge' => $hasBadge,
            ];
            return $user;
        });

        return $participants;
    }

    public function bulkUpdateChallenges(array $data): array
    {
        $challengeIds = $data['challenge_ids'];
        $updateData = $data['updates'];
        $successCount = 0;
        $failureCount = 0;
        $failedIds = [];

        DB::transaction(function () use ($challengeIds, $updateData, &$successCount, &$failureCount, &$failedIds) {
            $existingChallenges = ReadingChallenge::whereIn('id', $challengeIds)->pluck('id');
            $nonExistentIds = array_diff($challengeIds, $existingChallenges->toArray());

            if (!empty($nonExistentIds)) {
                $failureCount = count($nonExistentIds);
                $failedIds = $nonExistentIds;
                throw new \Exception('Invalid challenge IDs provided.');
            }

            $updatedRows = ReadingChallenge::whereIn('id', $challengeIds)->update($updateData);
            $successCount = $updatedRows;
        });

        return [
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'failed_ids' => $failedIds,
        ];
    }

    public function removeUserFromChallenge(int $challengeId, int $userId): void
    {
        UserChallengeBook::where('challenge_id', $challengeId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function resetUserProgress(int $challengeId, int $userId): void
    {
        DB::transaction(function () use ($challengeId, $userId) {
            UserChallengeBook::where('challenge_id', $challengeId)
                ->where('user_id', $userId)
                ->delete();

            $challenge = ReadingChallenge::find($challengeId);
            if ($challenge && $challenge->badge_id) {
                UserBadge::where('user_id', $userId)
                    ->where('badge_id', $challenge->badge_id)
                    ->where('challenge_id', $challengeId)
                    ->delete();
            }
        });
    }

    public function manuallyAwardBadge(int $userId, int $badgeId, ?int $challengeId = null): void
    {
        UserBadge::firstOrCreate(
            [
                'user_id' => $userId,
                'badge_id' => $badgeId,
            ],
            [
                'earned_at' => now(),
                'challenge_id' => $challengeId,
            ]
        );
    }

    public function manuallyRevokeBadge(int $userId, int $badgeId): void
    {
        UserBadge::where('user_id', $userId)
            ->where('badge_id', $badgeId)
            ->delete();
    }

   private function assignNextBook(int $userId, int $challengeId): void
    {
        $challenge = ReadingChallenge::with('books')->findOrFail($challengeId);
        $completedBookIds = UserChallengeBook::where('user_id', $userId)
            ->where('challenge_id', $challengeId)
            ->where('status', 'completed')
            ->pluck('book_id')
            ->toArray();

        $totalBooks = ChallengeBook::where('reading_challenge_id', $challengeId)->count();
        $completedBooks = count($completedBookIds);

        if ($completedBooks >= $totalBooks && $challenge->badge_id) {
            UserBadge::firstOrCreate(
                [
                    'user_id' => $userId,
                    'badge_id' => $challenge->badge_id,
                    'challenge_id' => $challengeId,
                ],
                ['earned_at' => now()]
            );
            return;
        }

        // Get all challenge books ordered by created_at
        $challengeBooks = ChallengeBook::where('reading_challenge_id', $challengeId)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($challengeBooks as $book) {
            // Check if user already has this book in their challenge reading list
            $exists = UserChallengeBook::where('user_id', $userId)
                ->where('challenge_id', $challengeId)
                ->where('book_id', $book->book_id)
                ->exists();

            if (!$exists) {
                $this->addUserBookToChallenge($challengeId, $userId, $book->book_id, 'reading', true);
                break;
            }
        }
    }
}

