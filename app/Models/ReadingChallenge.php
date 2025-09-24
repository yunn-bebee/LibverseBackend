<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    return $this->belongsToMany(
        Book::class,
        'challenge_books',
        'reading_challenge_id', // correct column
        'book_id'
    );
}
public function getIsCurrentAttribute(): bool
{
    $now = Carbon::now('Asia/Tokyo');
    $startDate = Carbon::parse($this->attributes['start_date']);
    $endDate = Carbon::parse($this->attributes['end_date']);

    return $startDate->lte($now) && $endDate->gte($now);
}


public function participants()
{
    return $this->belongsToMany(
        User::class,
        'user_challenge_books',
        'challenge_id', // foreign key on pivot table for ReadingChallenge
        'user_id'       // foreign key on pivot table for User
    )->withPivot('status', 'started_at', 'completed_at', 'user_rating', 'review');
}

}
