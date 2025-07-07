<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProfile extends Model
{
     use HasFactory; 

    protected $fillable = [
        'user_id', 'bio', 'profile_picture', 
        'website', 'location', 'reading_preferences'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
