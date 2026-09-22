<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Conversation_user;
use App\Models\User;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
   public function index(Request $request)
{
    $user = $request->user();

    $conversations = Conversation::whereHas(
        'conversationUsers',
        function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }
    )->get();

    return response()->json([
        'conversations' => $conversations
    ]);
}

// conversation users
public function show(Request $request, Conversation $conversation)
{
    $user = $request->user();

    $isParticipant = $conversation->conversationUsers()
        ->where('user_id', $user->id)
        ->exists();

    if (!$isParticipant) {
        abort(403, 'You are not a member of this conversation.');
    }

    return response()->json([
        'conversation' => $conversation->load('conversationUsers')
    ]);
}

}
