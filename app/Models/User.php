<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

      use HasFactory, Notifiable;

    protected $fillable = [
        'member_id', 'uuid', 'username', 'email', 'password', 
        'role', 'date_of_birth'
    ];

    protected $hidden = ['password', 'remember_token'];

    // Relationships
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class, 'added_by');
    }

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_likes');
    }

    public function savedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_saves');
    }

    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function rsvps()
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function challengeBooks()
    {
        return $this->hasMany(UserChallengeBook::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
                    ->withPivot('earned_at', 'challenge_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function mentions()
    {
        return $this->hasMany(Mention::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'followee_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'followee_id');
    }

    public function createdChallenges()
    {
        return $this->hasMany(ReadingChallenge::class, 'created_by');
    }
}
