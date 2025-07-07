<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
     use HasFactory; 
    protected $fillable = [
        'user_id', 'type', 'source_type', 'source_id', 
        'message', 'is_read'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
