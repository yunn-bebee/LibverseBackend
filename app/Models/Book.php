<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'library_book_id', 'isbn', 'title', 'author', 
        'cover_image', 'description', 'added_by', 'verified'
    ];

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function forums()
    {
        return $this->hasMany(Forum::class);
    }

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function challenges()
    {
        return $this->belongsToMany(ReadingChallenge::class, 'challenge_books');
    }

    public function userChallengeBooks()
    {
        return $this->hasMany(UserChallengeBook::class);
    }
}
