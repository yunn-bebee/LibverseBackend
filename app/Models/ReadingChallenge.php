<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReadingChallenge extends Model
{
     use HasFactory;
   protected $fillable = [
        'name', 'slug', 'description', 'start_date',
        'end_date', 'target_count', 'badge_id',
        'created_by', 'is_active'
    ];

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'challenge_books');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'user_challenge_books')
                    ->withPivot('status', 'started_at', 'completed_at', 'user_rating', 'review');
    }
}
