<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusViews extends Model
{
    //
    protected $fillable = [
        'user_id',
        'status_id'
    ];  
    // user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // status
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
