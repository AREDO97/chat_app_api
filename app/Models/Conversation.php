<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    // conversationUsers
    public function conversationUsers()
    {
        return $this->hasMany(Conversation_user::class);
    }
    // messages
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
   
}
