<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    //
    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size'
    ];
    // message
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public static function createForMessage(Message $message, $file, string $folder)
{
    $path = $file->store($folder, 'public');

    return self::create([
        'message_id' => $message->id,
        'file_name' => $file->getClientOriginalName(),
        'file_path' => $path,
        'mime_type' => $file->getMimeType(),
        'file_size' => $file->getSize(),
    ]);
}
}
