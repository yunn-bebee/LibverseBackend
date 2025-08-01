<?php

namespace Modules\Badge\App\Services;

use App\Models\Badge;
use Modules\Badge\App\Contracts\BadgeServiceInterface;

class BadgeService implements BadgeServiceInterface
{
    public function getAll()
    {
       return Badge::all();
    }

    public function find(string $id)
    {
        // TODO: Implement find() method
    }

    public function create(array $data)
    {
        // TODO: Implement create() method
    }

    public function update(string $id, array $data)
    {
        // TODO: Implement update() method
    }

    public function delete(string $id)
    {
        // TODO: Implement delete() method
    }
}
