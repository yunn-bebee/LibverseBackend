<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeBook extends Model
{
     protected $table = 'challenge_books';
    
    protected $fillable = ['challenge_id', 'book_id', 'added_by'];
    
    public $timestamps = true;

    public function challenge()
    {
        return $this->belongsTo(ReadingChallenge::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
