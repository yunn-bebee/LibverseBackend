<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;
use Laravel\Sanctum\HasApiTokens;
use App\Access\Permissions;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'member_id', 'uuid', 'username', 'email', 'password',  'approval_status', // Add this
        'approved_at', // Add this
        'role', 'date_of_birth', 'email_verified_at',
        'email_notifications', 'push_notifications'
    ];

    protected $hidden = ['password', 'remember_token'];


    // Approval status checks
    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    // Role checks
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN->value;
    }

    public function isModerator(): bool
    {
        return $this->role === UserRole::MODERATOR->value;
    }

    public function isMember(): bool
    {
        return $this->role === UserRole::MEMBER->value;
    }
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function hasPermission(string $permission): bool
    {
        return Permissions::hasPermission($this->role, $permission);
    }
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
