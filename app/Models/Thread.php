<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Thread extends Model
{
     use HasFactory; 
       protected $fillable = [
        'forum_id', 'user_id', 'title', 'content', 
        'post_type', 'is_pinned', 'is_locked', 'book_id'
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
