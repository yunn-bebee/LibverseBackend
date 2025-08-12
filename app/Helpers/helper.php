<?php

use Illuminate\Http\Request;

if (!function_exists('apiResponse')) {
    function apiResponse(bool $success, string $message = '', $data = null, int $statusCode = 200, array $errors = [], array $more = [])
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
            'errors'  => $errors,
            'meta'    => [
                'timestamp' => now()->toISOString(),
                'status'    => $statusCode
            ],
            $more
        ], $statusCode);
    }
}

if (!function_exists('getLimitOffsetFromRequest')) {
    function getLimitOffsetFromRequest(Request $request)
    {
        $offset = $request->offset;
        $limit = $request->limit;
        return [$limit, $offset];
    }
}

if (!function_exists('getNoPaginationPagPerPageFromRequest')) {
    function getNoPaginationPagPerPageFromRequest(Request $request)
    {
        $noPagination = boolval($request->noPagination);
        $pagPerPage = $request->pagPerPage;
        return [$noPagination, $pagPerPage];
    }
}
