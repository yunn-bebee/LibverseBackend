<?php

namespace Modules\Badge\App\Services;

use App\Models\Badge;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Badge\App\Contracts\BadgeServiceInterface;
use Illuminate\Http\UploadedFile;

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
    if (isset($data['icon_url']) && $data['icon_url'] instanceof UploadedFile) {
        $file = $data['icon_url'];
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception('Invalid badge icon type. Allowed: JPG, PNG, GIF, SVG.', 400);
        }

        $path = $file->store('badges', 'public');
        $fileUrl = Storage::url($path);

        $data['icon_url'] = $path; // store only relative path in DB
    }

    $badge = Badge::create($data);

    Log::info('Badge created', [
        'badge_id' => $badge->id,
        'name' => $badge->name,
    ]);

    return $badge;
}

public function update(string $id, array $data)
{
    $badge = Badge::find($id);

    if (!$badge) {
        return null;
    }

    if (isset($data['icon_url']) && $data['icon_url'] instanceof UploadedFile) {
        $file = $data['icon_url'];
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception('Invalid badge icon type. Allowed: JPG, PNG, GIF, SVG.', 400);
        }

        // delete old file if it exists
        if ($badge->icon_url && Storage::disk('public')->exists($badge->icon_url)) {
            Storage::disk('public')->delete($badge->icon_url);
        }

        // store new one
        $path = $file->store('badges', 'public');
        $data['icon_url'] = $path;
    } else {
        unset($data['icon_url']); // don’t overwrite accidentally
    }

    $badge->update($data);

    Log::info('Badge updated', [
        'badge_id' => $badge->id,
        'name' => $badge->name,
    ]);

    return $badge;
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
