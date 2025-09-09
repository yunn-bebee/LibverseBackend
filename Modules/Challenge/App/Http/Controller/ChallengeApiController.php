<?php

namespace Modules\Challenge\App\Http\Controller;

use App\Helpers\apiResponse;
use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\ReadingChallenge;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Challenge\App\Contracts\ChallengeServiceInterface;
use Modules\Challenge\App\Http\Requests\BulkUpdateChallengesRequest;
use Modules\Challenge\App\Http\Requests\ChallengeBookRequest;
use Modules\Challenge\App\Http\Requests\ChallengeRequest;
use Modules\Challenge\App\Http\Requests\ChallengeStatusRequest;
use Modules\Challenge\App\Resources\ChallengeApiResource;
use Modules\Challenge\App\Resources\UserProgressResource;


class ChallengeApiController extends Controller
{
    public function __construct(
        protected ChallengeServiceInterface $challengeService
    ) {}

    // Public endpoint - shows challenges without personal data
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['active', 'current']);
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
    }

    // User joins a challenge
    public function joinChallenge(string $challengeId): JsonResponse
    {
        $userId = Auth::id();
        $result = $this->challengeService->joinChallenge((int) $challengeId, $userId);

        return apiResponse(
            true,
            $result['message'],
            new ChallengeApiResource($result['challenge']),
            200
        );
    }

    // Get user's progress (only available after joining)
    public function getUserProgress(string $challengeId): JsonResponse
    {
        $userId = Auth::id();
        $progress = $this->challengeService->getUserProgress($userId, (int) $challengeId);

        return apiResponse(
            true,
            'Progress retrieved successfully',
            $progress,
            200
        );
    }

    // Add book to challenge (after joining)
  // Add book to challenge (after joining)
public function addBook(ChallengeBookRequest $request, string $challengeId): JsonResponse
{
    $userId = Auth::id();
    $validated = $request->validated();

    $this->challengeService->addBookToChallenge(
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
}
    // Update book status in challenge
    public function updateBookStatus(ChallengeStatusRequest $request, string $recordId): JsonResponse
    {
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
    }

    // Get challenge leaderboard
    public function getLeaderboard(string $challengeId): JsonResponse
    {
        $leaderboard = $this->challengeService->getLeaderboard((int) $challengeId);

        return apiResponse(
            true,
            'Leaderboard retrieved successfully',
            $leaderboard,
            200
        );
    }

    // Admin endpoints
    public function store(ChallengeRequest $request): JsonResponse
    {
        $challenge = $this->challengeService->create($request->validated());

        return apiResponse(
            true,
            'Challenge created successfully',
            new ChallengeApiResource($challenge),
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $challenge = $this->challengeService->find((int) $id);

        if (!$challenge) {
            return apiResponse(false, 'Challenge not found', null, 404);
        }

        return apiResponse(
            true,
            'Challenge retrieved successfully',
            new ChallengeApiResource($challenge),
            200
        );
    }

    public function update(ChallengeRequest $request, string $id): JsonResponse
    {
        $challenge = $this->challengeService->update((int) $id, $request->validated());

        return apiResponse(
            true,
            'Challenge updated successfully',
            new ChallengeApiResource($challenge),
            200
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->challengeService->delete((int) $id);

        if (!$deleted) {
            return apiResponse(false, 'Challenge not found', null, 404);
        }

        return apiResponse(
            true,
            'Challenge deleted successfully',
            null,
            204
        );
    }
     /**
     * Get paginated list of users and their progress for a specific challenge.
     */
    public function getChallengeParticipants(Request $request, ReadingChallenge $challenge): JsonResponse
    {
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
    }

    /**
     * Update multiple challenges at once.
     */
    public function bulkUpdate(BulkUpdateChallengesRequest $request): JsonResponse
    {
        $result = $this->challengeService->bulkUpdateChallenges($request->validated());
        return apiResponse(
            true,
            "Successfully updated {$result['success_count']} challenges. Failed to update {$result['failure_count']}.",
            $result,
            200
        );
    }

    /**
     * Remove a user from a challenge.
     */
    public function removeUserFromChallenge(ReadingChallenge $challenge, User $user): JsonResponse
    {
        $this->challengeService->removeUserFromChallenge($challenge->id, $user->id);
        return apiResponse(true, 'User successfully removed from challenge.', null, 200);
    }

    /**
     * Reset a user's progress in a challenge.
     */
    public function resetUserProgress(ReadingChallenge $challenge, User $user): JsonResponse
    {
        $this->challengeService->resetUserProgress($challenge->id, $user->id);
        return apiResponse(true, "User's progress has been successfully reset.", null, 200);
    }

    /**
     * Manually award a badge to a user.
     */
    public function manuallyAwardBadge(Request $request, User $user): JsonResponse
    {
        $request->validate(['badge_id' => 'required|exists:badges,id']);
        $badgeId = $request->input('badge_id');
        $challengeId = $request->input('challenge_id'); // Optional

        $this->challengeService->manuallyAwardBadge($user->id, $badgeId, $challengeId);
        return apiResponse(true, 'Badge awarded successfully.', null, 200);
    }

    /**
     * Revoke a badge from a user.
     */
    public function manuallyRevokeBadge(User $user, Badge $badge): JsonResponse
    {
        $this->challengeService->manuallyRevokeBadge($user->id, $badge->id);
        return apiResponse(true, 'Badge revoked successfully.', null, 200);
    }

}
