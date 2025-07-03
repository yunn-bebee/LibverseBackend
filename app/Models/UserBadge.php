<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
      protected $table = 'user_badges';
    
    protected $fillable = ['user_id', 'badge_id', 'earned_at', 'challenge_id'];
    
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    public function challenge()
    {
        return $this->belongsTo(ReadingChallenge::class);
    }
}
