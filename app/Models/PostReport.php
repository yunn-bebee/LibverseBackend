<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PostReport extends Model
{
  use HasFactory;

    protected $fillable = [
        'post_id', 'user_id', 'reason', 'status', 'reviewed_at', 'reviewed_by'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
};
