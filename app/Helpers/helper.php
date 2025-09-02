<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Notification\App\Notifications\GenericNotification;



if (!function_exists('apiResponse')) {
    /**
     * Standard API response format with pagination support for Axios clients
     *
     * @param bool $success Whether the request was successful
     * @param string $message Human-readable message
     * @param mixed $data The response payload
     * @param int $statusCode HTTP status code
     * @param array $errors Array of error details
     * @param LengthAwarePaginator|null $paginator Paginator instance
     * @param array $additional Additional metadata
     * @return JsonResponse
     */
    function apiResponse(
        bool $success,
        string $message = '',
        $data = null,
        int $statusCode = 200,
        array $errors = [],
        ?LengthAwarePaginator $paginator = null,
        array $additional = []
    ): JsonResponse {
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => (object)$errors, // Ensure errors is always an object for consistency
            'meta' => [
                'timestamp' => now()->toISOString(),
                'status' => $statusCode,
            ],
        ];

        // Add pagination data in a format that works well with Axios
        if ($paginator) {
            $response['meta']['pagination'] = [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ];
        }

        // Merge additional data without overwriting core fields
        foreach ($additional as $key => $value) {
            if (!isset($response[$key])) {
                $response[$key] = $value;
            }
        }

        return response()->json($response, $statusCode, [], JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('getPaginationParams')) {
    /**
     * Get standardized pagination parameters from request with validation
     *
     * @param Request $request
     * @return array [perPage, page, noPagination]
     */
    function getPaginationParams(Request $request): array
    {
        $perPage = max(1, min(100, (int)$request->input('per_page', 15)));
        $page = max(1, (int)$request->input('page', 1));

        return [
            'perPage' => $perPage,
            'page' => $page,
            'noPagination' => filter_var($request->input('no_pagination', false), FILTER_VALIDATE_BOOL),
        ];
    }
}

if (!function_exists('paginateIfNeeded')) {
    /**
     * Apply pagination to query builder if needed with type safety
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param array $paginationParams [perPage, page, noPagination]
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    function paginateIfNeeded($query, array $paginationParams)
    {
        if ($paginationParams['noPagination']) {
            return $query->get();
        }

        return $query->paginate(
            $paginationParams['perPage'],
            ['*'],
            'page',
            $paginationParams['page']
        );
    }
}

if (!function_exists('successResponse')) {
    /**
     * Helper for successful responses
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @param LengthAwarePaginator|null $paginator
     * @param array $additional
     * @return JsonResponse
     */
    function successResponse(
        $data = null,
        string $message = 'Operation successful',
        int $statusCode = 200,
        ?LengthAwarePaginator $paginator = null,
        array $additional = []
    ): JsonResponse {
        return apiResponse(true, $message, $data, $statusCode, [], $paginator, $additional);
    }
}

if (!function_exists('errorResponse')) {
    /**
     * Helper for error responses
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @param mixed $data
     * @return JsonResponse
     */
    function errorResponse(
        string $message = 'An error occurred',
        array $errors = [],
        int $statusCode = 400,
        $data = null
    ): JsonResponse {
        return apiResponse(false, $message, $data, $statusCode, $errors);
    }
}

if (! function_exists('send_notification')) {
    /**
     * Send a notification to a user.
     *
     * @param User $user The user to notify
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string|null $actionUrl URL for the action button
     * @param string|null $actionText Text for the action button
     * @return bool Whether the notification was sent successfully
     */
    function send_notification(User $user, string $title, string $message, ?string $actionUrl = null, ?string $actionText = null): bool
    {
        try {
            $user->notify(new GenericNotification($user, $title, $message, $actionUrl, $actionText));
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
            return false;
        }
    }
}
