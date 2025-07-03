<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
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
}
