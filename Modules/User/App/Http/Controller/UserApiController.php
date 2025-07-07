<?php

namespace Modules\User\App\Http\Controller;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\User\App\Contracts\UserServiceInterface;
use Modules\User\App\Http\Requests\StoreUserApiRequest;
use Modules\User\App\Http\Requests\UpdateUserApiRequest;

class UserApiController extends Controller
{
    public function __construct(protected UserServiceInterface $userService) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserApiRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserApiRequest $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
