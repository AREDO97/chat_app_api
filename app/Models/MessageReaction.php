<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageReaction extends Model
{
    // allowed
    protected $fillable = [
        'message_id',
        'user_id',
        'reaction'
    ];
    // user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // message
    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
