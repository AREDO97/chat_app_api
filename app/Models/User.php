<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
#[Fillable(['name', 'email', 'password','role','is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens,HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // logs
    public function userLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
    // conversation_users
    public function conversationUsers()
    {
        return $this->hasMany(Conversation_user::class);
    }
     // reactions
    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }
    // bookmark
    public function bookmarks()
    {
        return $this->hasMany(MessageBookmark::class);
    }
}
