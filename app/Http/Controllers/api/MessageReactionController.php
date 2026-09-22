<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageReactionController extends Controller
{
    // react to message
    public function reactToMessage(Request $request,Message $message)
    {
        $user=$request->user();
        $allowedUsers=$message->conversation->conversationUsers()->where('user_id',$user->id)->exists();
        if (! $allowedUsers)
            {
                abort(403,'Unauthorized action');
            }
        //validation
        $request->validate([
            'reaction'=>'required|string|max:10'
        ]);
        // check if user already reacted to message
       $reactionCheck = MessageReaction::where('user_id',$user->id)->where('message_id',$message->id)->first();
       if (! $reactionCheck)
        {
          // create reaction
       $reaction = MessageReaction::create([
            'message_id'=>$message->id,
            'user_id'=>$user->id,
            'reaction'=>$request->reaction
        ]);
        // response
         return response()->json($reaction);

        }
        else
            {
  // otherwise update reaction
        $reactionCheck->update([
            'reaction'=>$request->reaction
        ]);
            }
      
      
             $reactionCounts = MessageReaction::where('message_id', $message->id)
    ->select('reaction', DB::raw('COUNT(*) as count'))
    ->groupBy('reaction')
    ->get();
    
        // response
        return response()->json([
            'reactions'=>$reactionCheck,
            'reaction_count'=>$reactionCounts
        ]);
    }
    
}

