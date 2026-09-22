<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageBookmark extends Model
{
    // allowed
    protected $fillable = [
        'user_id',
        'message_id',
        'is_deleted'
    ];
    //user
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
