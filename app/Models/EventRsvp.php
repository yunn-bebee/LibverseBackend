<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventRsvp extends Model
{
     use HasFactory; 
   protected $table = 'event_rsvps';
    
    protected $fillable = [
        'user_id', 'event_id', 'attendance_type', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
