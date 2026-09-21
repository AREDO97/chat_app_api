<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation_user extends Model
{
    //
    protected $fillable = [
        'user_id',
        'conversation_id'
    ];
    // coversation 
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
    // user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
