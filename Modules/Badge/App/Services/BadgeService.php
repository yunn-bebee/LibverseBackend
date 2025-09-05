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
        return Badge::find($id);
    }

    public function create(array $data)
    {
        return Badge::create($data);
    }

    public function update(string $id, array $data)
    {
        $badge = Badge::find($id);
        if ($badge) {
            $badge->update($data);
            return $badge;
        }
        return null;
    }



    public function delete(string $id)
    {
        $badge = Badge::find($id);
        if ($badge) {
            $badge->delete();
            return true;
        }
        return false;
    }
}
