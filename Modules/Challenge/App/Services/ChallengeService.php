<?php

namespace Modules\Challenge\App\Services;

use Modules\Challenge\App\Contracts\ChallengeServiceInterface;
use App\Models\UserChallengeBook;
use App\Models\ChallengeBook;
use App\Models\Badge;
use App\Models\ReadingChallenge;
use App\Models\UserBadge;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ChallengeService implements ChallengeServiceInterface
{
    public function getAll(array $filters = [], int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        $query = ReadingChallenge::with(['badge', 'books', 'creator']);

        // Only show active challenges to non-admins
        if (!Auth::check() || (Auth::check() && !Auth::user()->isAdmin())) {
            $query->where('is_active', true)
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
        }

        $challenges = $query->orderBy('created_at', 'desc')
                          ->paginate($perPage, ['*'], 'page', $page);

        // Add joined status and progress for each challenge if user is authenticated
        if (Auth::check()) {
            $userId = Auth::id();
            $challenges->getCollection()->transform(function ($challenge) use ($userId) {
                $challenge->has_joined = $this->hasUserJoined($userId, $challenge->id);
                if ($challenge->has_joined) {
                    $challenge->progress = $this->getUserProgress($userId, $challenge->id);
                }
                return $challenge;
            });
        }

        return $challenges;
    }

    public function find(int $id): ?ReadingChallenge
    {
        $challenge = ReadingChallenge::with(['badge', 'books', 'creator'])->find($id);

        if (Auth::check() && $challenge) {
            $challenge->has_joined = $this->hasUserJoined(Auth::id(), $challenge->id);
            if ($challenge->has_joined) {
                $challenge->progress = $this->getUserProgress(Auth::id(), $challenge->id);
            }
        }

        return $challenge;
    }

    public function create(array $data): ReadingChallenge
    {
        return DB::transaction(function () use ($data) {
            // Validate at least one book is provided
            if (!isset($data['book_ids']) || !is_array($data['book_ids']) || empty($data['book_ids'])) {
                throw new \Exception('At least one book is required to create a challenge.');
            }

            $data['created_by'] = Auth::id();
            $challenge = ReadingChallenge::create($data);

            // Add books
            foreach ($data['book_ids'] as $bookId) {
                ChallengeBook::create([
                    'reading_challenge_id' => $challenge->id,
                    'book_id' => $bookId,
                    'added_by' => Auth::id(),
                ]);
            }

            return $challenge->load('badge', 'creator', 'books');
        });
    }

    public function update(int $id, array $data): ReadingChallenge
    {
        return DB::transaction(function () use ($id, $data) {
            $challenge = ReadingChallenge::findOrFail($id);

            // Validate at least one book is provided if book_ids is present
            if (isset($data['book_ids']) && (empty($data['book_ids']) || !is_array($data['book_ids']))) {
                throw new \Exception('At least one book is required when updating challenge books.');
            }

            $challenge->update($data);

            // Update books if provided
            if (isset($data['book_ids'])) {
                ChallengeBook::where('reading_challenge_id', $challenge->id)->delete();
                foreach ($data['book_ids'] as $bookId) {
                    ChallengeBook::create([
                        'reading_challenge_id' => $challenge->id,
                        'book_id' => $bookId,
                        'added_by' => Auth::id(),
                    ]);
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
            // Check if already joined
            if ($this->hasUserJoined($userId, $challengeId)) {
                throw new \Exception('You have already joined this challenge.');
            }

            $challenge = ReadingChallenge::with('books')->findOrFail($challengeId);

            // Validate challenge is joinable
            if (!$challenge->is_active || $challenge->start_date > now() || $challenge->end_date < now()) {
                throw new \Exception('This challenge is not currently available to join.');
            }

            // Get the first book, ordered by creation time
            $firstBook = ChallengeBook::where('reading_challenge_id', $challengeId)
                                     ->orderBy('created_at', 'asc')
                                     ->first();

            if (!$firstBook) {
                throw new \Exception('No books available in this challenge.');
            }

            // Create user challenge record with the first book
            UserChallengeBook::create([
                'user_id' => $userId,
                'challenge_id' => $challengeId,
                'book_id' => $firstBook->book_id,
                'status' => 'reading',
                'started_at' => now(),
            ]);

            return [
                'challenge' => $challenge,
                'message' => 'Successfully joined the challenge and started reading the first book!'
            ];
        });
    }

  public function addBookToChallenge(int $challengeId, int $userId, int $bookId, string $status): bool
    {
        return DB::transaction(function () use ($challengeId, $userId, $bookId, $status) {
            // Check if user has joined the challenge
            if (!$this->hasUserJoined($userId, $challengeId)) {
                throw new \Exception('You must join the challenge before adding a book.');
            }

            $user = Auth::user();
            $isAdminOrModerator = $user && ($user->hasRole('admin') || $user->hasRole('moderator'));

            // Check if the book is part of the challenge's book list
            $challengeBook = ChallengeBook::where('reading_challenge_id', $challengeId)
                                         ->where('book_id', $bookId)
                                         ->first();

            // If user is admin or moderator, allow adding the book to ChallengeBook table if not already present
            if ($isAdminOrModerator && !$challengeBook) {
                ChallengeBook::create([
                    'reading_challenge_id' => $challengeId,
                    'book_id' => $bookId,
                    'added_by' => $userId,
                ]);
                // Update challengeBook to reflect the new entry for the UserChallengeBook creation below
                $challengeBook = ChallengeBook::where('reading_challenge_id', $challengeId)
                                             ->where('book_id', $bookId)
                                             ->first();
            }

            // For regular users, validate that the book is part of the challenge's book list
            if (!$isAdminOrModerator && !$challengeBook) {
                throw new \Exception('The selected book is not part of this challenge.');
            }

            // Check if the book is already in the user's challenge reading list
            $existingRecord = UserChallengeBook::where('user_id', $userId)
                                              ->where('challenge_id', $challengeId)
                                              ->where('book_id', $bookId)
                                              ->first();

            if ($existingRecord) {
                throw new \Exception('This book is already in your challenge reading list.');
            }

            // Create a new user challenge book record
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
    public function updateBookStatus(int $recordId, string $status, int $rating = null, string $review = null): bool
    {
        return DB::transaction(function () use ($recordId, $status, $rating, $review) {
            $record = UserChallengeBook::findOrFail($recordId);
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

        return [
            'challenge_id' => $challengeId,
            'challenge_name' => $challenge->name,
            'start_date' => $challenge->start_date,
            'end_date' => $challenge->end_date,
            'total_books' => $totalBooks,
            'books_read' => $completedBooks,
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
            ->with('user')
            ->get();

        return $leaderboard->map(function ($item) {
            return [
                'user' => [
                    'id' => $item->user->id,
                    'name' => $item->user->name,
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

    private function assignNextBook(int $userId, int $challengeId): void
    {
        $challenge = ReadingChallenge::with('books')->findOrFail($challengeId);
        $completedBookIds = UserChallengeBook::where('user_id', $userId)
            ->where('challenge_id', $challengeId)
            ->where('status', 'completed')
            ->pluck('book_id')
            ->toArray();

        // Find the next book that hasn't been completed, ordered by creation time
        $nextBook = ChallengeBook::where('reading_challenge_id', $challengeId)
            ->whereNotIn('book_id', $completedBookIds)
            ->orderBy('created_at', 'asc')
            ->first();

        $totalBooks = ChallengeBook::where('reading_challenge_id', $challengeId)->count();
        $completedBooks = count($completedBookIds);

        if ($completedBooks >= $totalBooks && $challenge->badge_id) {
            // Award badge if all books are completed
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

        if ($nextBook) {
            UserChallengeBook::create([
                'user_id' => $userId,
                'challenge_id' => $challengeId,
                'book_id' => $nextBook->book_id,
                'status' => 'reading',
                'started_at' => now(),
            ]);
        }
    }

}
