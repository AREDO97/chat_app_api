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
    // attachments
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
    // reactions
    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }
    // bookmarks
    public function bookmarks()
{
    return $this->hasMany(MessageBookmark::class);
}

}
