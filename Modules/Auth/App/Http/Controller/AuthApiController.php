<?php

namespace Modules\Auth\App\Http\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\App\Http\Requests\LoginRequest;
use Modules\Auth\App\Http\Requests\RegisterRequest;
use Modules\Auth\App\Contracts\AuthServiceInterface;

class AuthApiController extends Controller
{
    public function __construct(
        protected AuthServiceInterface $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());
        return apiResponse(
            true,
            'User registered successfully',
            $user,
            201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login($request->validated());
        return apiResponse(
            true,
            'Login successful',
            $data
        );
    }

    public function logout(): JsonResponse
    {
         $this->authService->logout();
        return apiResponse(
            true,
            'Successfully logged out'
        );
    }

}
