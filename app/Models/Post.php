<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
     use HasFactory;
      protected $fillable = [
        'thread_id', 'user_id', 'content',
        'is_flagged', 'parent_post_id', 'book_id'
    ];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function parentPost()
    {
        return $this->belongsTo(Post::class, 'parent_post_id');
    }

    public function replies()
    {
        return $this->hasMany(Post::class, 'parent_post_id');
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'post_likes');
    }

    public function saves()
    {
        return $this->belongsToMany(User::class, 'post_saves');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
