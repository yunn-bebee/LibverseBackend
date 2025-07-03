<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
     protected $fillable = [
        'title', 'slug', 'description', 'event_type', 
        'start_time', 'end_time', 'location_type', 
        'physical_address', 'zoom_link', 'max_attendees', 
        'cover_image', 'created_by', 'forum_id'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function rsvps()
    {
        return $this->hasMany(EventRsvp::class);
    }
}
