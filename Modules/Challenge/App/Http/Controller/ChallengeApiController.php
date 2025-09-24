<?php

namespace Modules\Challenge\App\Http\Controller;

use App\Helpers\apiResponse;
use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\ReadingChallenge;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Book\App\Resources\BookApiResource;
use Modules\Challenge\App\Contracts\ChallengeServiceInterface;
use Modules\Challenge\App\Http\Requests\AddBookRequest;
use Modules\Challenge\App\Http\Requests\BulkUpdateChallengesRequest;
use Modules\Challenge\App\Http\Requests\ChallengeBookRequest;
use Modules\Challenge\App\Http\Requests\ChallengeRequest;
use Modules\Challenge\App\Http\Requests\ChallengeStatusRequest;
use Modules\Challenge\App\Resources\ChallengeApiResource;
use Modules\Challenge\App\Resources\UserProgressResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChallengeApiController extends Controller
{
    public function __construct(
        protected ChallengeServiceInterface $challengeService
    ) {}

    // Public endpoint - shows challenges without personal data
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['active', 'current' , 'search', 'is_joined']);
            $paginationParams = getPaginationParams($request);

            $challenges = $this->challengeService->getAll(
                $filters,
                $paginationParams['perPage'],
                $paginationParams['page']
            );

            return apiResponse(
                true,
                'Challenges retrieved successfully',
                ChallengeApiResource::collection($challenges),
                200,
                [],
                $challenges
            );
        } catch (Exception $e) {
            Log::error('Failed to retrieve challenges: ' . $e->getMessage());
            return errorResponse('Failed to retrieve challenges', [], 500);
        }
    }

    // User joins a challenge
    public function joinChallenge(string $challengeId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $result = $this->challengeService->joinChallenge((int) $challengeId, $userId);

            return apiResponse(
                true,
                $result['message'],
                new ChallengeApiResource($result['challenge']),
                200
            );
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (ValidationException $e) {
            return errorResponse($e->getMessage(), $e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Failed to join challenge: ' . $e->getMessage());
            return errorResponse($e->getMessage(), ['message' => $e->getMessage()], 423);
        }
    }

    // Get user's progress (only available after joining)
    public function getUserProgress(string $challengeId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $progress = $this->challengeService->getUserProgress($userId, (int) $challengeId);

            return apiResponse(
                true,
                'Progress retrieved successfully',
                $progress,
                200
            );
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (Exception $e) {
            Log::error('Failed to get user progress: ' . $e->getMessage());
            return errorResponse('Failed to get user progress', [], 500);
        }
    }

    // Add book to challenge (after joining)
    public function addUserBook(ChallengeBookRequest $request, string $challengeId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $validated = $request;

            $this->challengeService->addUserBookToChallenge(
                (int) $challengeId,
                $userId,
                $validated['book_id'],
                $validated['status']
            );

            return apiResponse(
                true,
                'Book added to challenge successfully',
                null,
                200
            );
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (ValidationException $e) {
            return errorResponse($e->getMessage(), $e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Failed to add book to challenge: ' . $e->getMessage());
            return errorResponse($e->getMessage(), [], 500);
        }
    }

    public function removeUserBook(ChallengeBookRequest $request, string $challengeId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $validated = $request->validated();

            $this->challengeService->removeUserBookFromChallenge(
                (int) $challengeId,
                $userId,
                $validated['book_id']
            );

            return apiResponse(
                true,
                'Book removed from challenge successfully',
                null,
                200
            );
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (Exception $e) {
            Log::error('Failed to remove book from challenge: ' . $e->getMessage());
            return errorResponse('Failed to remove book from challenge', [], 500);
        }
    }

    // Update book status in challenge
    public function updateBookStatus(ChallengeStatusRequest $request, string $recordId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->challengeService->updateBookStatus(
                (int) $recordId,
                $validated['status'],
                $validated['rating'],
                $validated['review']
            );

            return apiResponse(
                true,
                'Book status updated successfully',
                null,
                200
            );
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (ValidationException $e) {
            return errorResponse($e->getMessage(), $e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Failed to update book status: ' . $e->getMessage());
            return errorResponse($e->getMessage(), [], 500);
        }
    }

    // Get challenge leaderboard
    public function getLeaderboard(string $challengeId): JsonResponse
    {
        try {
            $leaderboard = $this->challengeService->getLeaderboard((int) $challengeId);

            return apiResponse(
                true,
                'Leaderboard retrieved successfully',
                $leaderboard,
                200
            );
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (Exception $e) {
            Log::error('Failed to get leaderboard: ' . $e->getMessage());
            return errorResponse($e->getMessage(), [], 500);
        }
    }

    // Admin endpoints
    public function store(ChallengeRequest $request): JsonResponse
    {
        try {
            $challenge = $this->challengeService->create($request->validated());

            return apiResponse(
                true,
                'Challenge created successfully',
                new ChallengeApiResource($challenge),
                201
            );
        } catch (ValidationException $e) {
            return errorResponse($e->getMessage(), $e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Failed to create challenge: ' . $e->getMessage());
            return errorResponse('Failed to create challenge', [], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $challenge = $this->challengeService->find((int) $id);

            if (!$challenge) {
                return errorResponse('Challenge not found', [], 404);
            }

            return apiResponse(
                true,
                'Challenge retrieved successfully',
                new ChallengeApiResource($challenge),
                200
            );
        } catch (Exception $e) {
            Log::error('Failed to retrieve challenge: ' . $e->getMessage());
            return errorResponse('Failed to retrieve challenge', [], 500);
        }
    }

    public function update(ChallengeRequest $request, string $id): JsonResponse
    {
        try {
            $challenge = $this->challengeService->update((int) $id, $request->validated());

            if (!$challenge) {
                return errorResponse('Challenge not found', [], 404);
            }

            return apiResponse(
                true,
                'Challenge updated successfully',
                new ChallengeApiResource($challenge),
                200
            );
        } catch (ValidationException $e) {
            return errorResponse($e->getMessage(), $e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Failed to update challenge: ' . $e->getMessage());
            return errorResponse('Failed to update challenge', [], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->challengeService->delete((int) $id);

            if (!$deleted) {
                return errorResponse('Challenge not found', [], 404);
            }

            return apiResponse(
                true,
                'Challenge deleted successfully',
                null,
                204
            );
        } catch (Exception $e) {
            Log::error('Failed to delete challenge: ' . $e->getMessage());
            return errorResponse('Failed to delete challenge', [], 500);
        }
    }

    /**
     * Get paginated list of users and their progress for a specific challenge.
     */
    public function getChallengeParticipants(Request $request, ReadingChallenge $challenge): JsonResponse
    {
        try {
            $paginationParams = getPaginationParams($request);
            $participants = $this->challengeService->getChallengeParticipantsProgress(
                $challenge->id,
                $paginationParams['perPage'],
                $paginationParams['page']
            );

            return apiResponse(
                true,
                'Challenge participants retrieved successfully',
                UserProgressResource::collection($participants),
                200,
                [],
                $participants
            );
        } catch (Exception $e) {
            Log::error('Failed to get challenge participants: ' . $e->getMessage());
            return errorResponse('Failed to get challenge participants', [], 500);
        }
    }

    /**
     * Update multiple challenges at once.
     */
    public function bulkUpdate(BulkUpdateChallengesRequest $request): JsonResponse
    {
        try {
            $result = $this->challengeService->bulkUpdateChallenges($request->validated());
            return apiResponse(
                true,
                "Successfully updated {$result['success_count']} challenges. Failed to update {$result['failure_count']}.",
                $result,
                200
            );
        } catch (ValidationException $e) {
            return errorResponse($e->getMessage(), $e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Failed to bulk update challenges: ' . $e->getMessage());
            return errorResponse('Failed to bulk update challenges', [], 500);
        }
    }

    /**
     * Remove a user from a challenge.
     */
    public function removeUserFromChallenge(ReadingChallenge $challenge, User $user): JsonResponse
    {
        try {
            $this->challengeService->removeUserFromChallenge($challenge->id, $user->id);
            return apiResponse(true, 'User successfully removed from challenge.', null, 200);
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (Exception $e) {
            Log::error('Failed to remove user from challenge: ' . $e->getMessage());
            return errorResponse('Failed to remove user from challenge', [], 500);
        }
    }

    /**
     * Reset a user's progress in a challenge.
     */
    public function resetUserProgress(ReadingChallenge $challenge, User $user): JsonResponse
    {
        try {
            $this->challengeService->resetUserProgress($challenge->id, $user->id);
            return apiResponse(true, "User's progress has been successfully reset.", null, 200);
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (Exception $e) {
            Log::error('Failed to reset user progress: ' . $e->getMessage());
            return errorResponse('Failed to reset user progress', [], 500);
        }
    }

    /**
     * Manually award a badge to a user.
     */
    public function manuallyAwardBadge(Request $request, User $user): JsonResponse
    {
        try {
            $request->validate(['badge_id' => 'required|exists:badges,id']);
            $badgeId = $request->input('badge_id');
            $challengeId = $request->input('challenge_id'); // Optional

            $this->challengeService->manuallyAwardBadge($user->id, $badgeId, $challengeId);
            return apiResponse(true, 'Badge awarded successfully.', null, 200);
        } catch (ValidationException $e) {
            return errorResponse($e->getMessage(), $e->errors(), 422);
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (Exception $e) {
            Log::error('Failed to award badge: ' . $e->getMessage());
            return errorResponse('Failed to award badge', [], 500);
        }
    }

    /**
     * Revoke a badge from a user.
     */
    public function manuallyRevokeBadge(User $user, Badge $badge): JsonResponse
    {
        try {
            $this->challengeService->manuallyRevokeBadge($user->id, $badge->id);
            return apiResponse(true, 'Badge revoked successfully.', null, 200);
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (Exception $e) {
            Log::error('Failed to revoke badge: ' . $e->getMessage());
            return errorResponse('Failed to revoke badge', [], 500);
        }
    }

    public function getBooks(Request $request, string $id): JsonResponse
    {
        try {
            $paginationParams = getPaginationParams($request);
            $books = $this->challengeService->getBooks(
                (int) $id,
                $paginationParams['perPage'],
                $paginationParams['page']
            );

            return apiResponse(
                true,
                'Books retrieved successfully',
                BookApiResource::collection($books->pluck('book')),
                200,
                [],
                $books
            );
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (Exception $e) {
            Log::error('Failed to get books: ' . $e->getMessage());
            return errorResponse('Failed to get books', [], 500);
        }
    }

    public function addBook(AddBookRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->challengeService->addBookToChallenge((int) $id, (int) $validated['book_id']);

            return apiResponse(
                true,
                'Book added successfully',
                null,
                200
            );
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (ValidationException $e) {
            return errorResponse($e->getMessage(), $e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Failed to add book: ' . $e->getMessage());
            return errorResponse($e->getMessage(), [], 500);
        }
    }

    public function removeBook(string $id, string $bookId): JsonResponse
    {
        try {
            $this->challengeService->removeBookFromChallenge((int) $id, (int) $bookId);

            return apiResponse(
                true,
                'Book removed successfully',
                null,
                200
            );
        } catch (NotFoundHttpException $e) {
            return errorResponse($e->getMessage(), [], 404);
        } catch (Exception $e) {
            Log::error('Failed to remove book: ' . $e->getMessage());
            return errorResponse('Failed to remove book', [], 500);
        }
    }
}
