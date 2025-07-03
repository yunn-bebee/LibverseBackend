<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFollow extends Model
{
       protected $table = 'user_follows';
    
    protected $fillable = ['follower_id', 'followee_id'];
    
    public $timestamps = true;

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function followee()
    {
        return$this->belongsTo(User::class, 'followee_id');
    }

}
