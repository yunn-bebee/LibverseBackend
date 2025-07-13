<?php

namespace Modules\User\App\Services;

use Modules\User\App\Contracts\UserServiceInterface;
use App\Models\User;

class UserService implements UserServiceInterface {
    public function get($id)
    {
        User::get( $id);
    }

    public function getAll()
    {
        User::all();
    }

    public function save()
    {
        
    }

    public function update()
    {
        
    }

    public function delete($id)
    {
        User::delete($id);
    }
}
