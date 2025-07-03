<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id', 'bio', 'profile_picture', 
        'website', 'location', 'reading_preferences'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
