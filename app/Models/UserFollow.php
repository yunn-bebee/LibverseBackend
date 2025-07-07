<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFollow extends Model
{
     use HasFactory;    
    protected $table = 'user_follows';
    
    protected $fillable = ['follower_id', 'followee_id'];
    
   

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function followee()
    {
        return$this->belongsTo(User::class, 'followee_id');
    }

}
