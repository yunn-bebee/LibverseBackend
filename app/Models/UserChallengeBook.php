<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChallengeBook extends Model
{
    protected $table = 'user_challenge_books';
    
    protected $fillable = [
        'user_id', 'challenge_id', 'book_id', 'status',
        'started_at', 'completed_at', 'user_rating', 'review'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(ReadingChallenge::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
