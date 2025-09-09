<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badge extends Model
{
     use HasFactory;
      protected $fillable = [
        'name', 'icon_url', 'description', 'type'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges');
    }

    public function challenges()
    {
        return $this->hasMany(ReadingChallenge::class);
    }
      // Accessor to always return full URL
public function getIconUrlAttribute($value)
{
    if (!$value) {
        return null;
    }

    // Generate /storage/... first
    $relativeUrl = Storage::url($value);

    // Prepend APP_URL
    return config('app.url') . $relativeUrl;
}
}
