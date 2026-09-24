<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    //
    protected $fillable = [
        'user_id',
        'expires_at',
        'content',
        'type',
        'media_path'
    ];
    // user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // view
    public function views()
    {
        return $this->hasMany(StatusViews::class);
    }
}
