<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'is_deleted',
    ];
    // conversation
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
