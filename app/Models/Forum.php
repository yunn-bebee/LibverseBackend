<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Forum extends Model
{
     use HasFactory; 
    protected $fillable = [
        'name', 'slug', 'description', 'category', 
        'is_public', 'created_by', 'book_id'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
