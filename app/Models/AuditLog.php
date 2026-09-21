<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLog extends Model
{
    /*
    action
ip_address
user_agent
body* */
protected $fillable = [
    'user_id',
    'action',
    'ip_address',
    'user_agent',
    'body'
];
// body to array
  protected $casts = [
        'body' => 'array',
    ];
// user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
// self injection
public static function logAction(Request $request,$userId,$action,$body)
{
    $logs = self::create([
        'user_id'=>$userId,
        'action'=>$action,
        'ip_address'=>$request->ip(),
        'user_agent'=>$request->userAgent(),
        'body'=>$body
    ]);
    return $logs;
}
}
