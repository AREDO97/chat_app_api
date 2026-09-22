<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PinnedMessage;
use App\Models\Message;

use Illuminate\Http\Request;

class PinnedController extends Controller
{
    //pin a message
     public function create(Request $request, Message $message)
{
    $user = $request->user();

    $allowedUsers = $message->conversation
        ->conversationUsers()
        ->where('user_id', $user->id)
        ->exists();

    if (! $allowedUsers) {
        abort(403, 'Unauthorized action');
    }

    $request->validate([
        'duration' => ['required', 'in:24_hours,7_days,30_days']
    ]);

    $expiresAt = match ($request->duration) {
        '24_hours' => now()->addHours(24),
        '7_days' => now()->addDays(7),
        '30_days' => now()->addDays(30),
    };

    $pinnedMessage = PinnedMessage::create([
        'user_id' => $user->id,
        'message_id' => $message->id,
        'expires_at' => $expiresAt,
    ]);

    return response()->json([
        'pinned_message' => $pinnedMessage,
        'message' => $message
    ]);
}

// display pinned messages
public function index(Request $request)
{
    $user=$request->user();
  $pinnedMessage = PinnedMessage::where('user_id', $user->id)
    ->where('expires_at', '>', now())
    ->with('message')
    ->get();
    // response
    return response()->json($pinnedMessage);
}
}
