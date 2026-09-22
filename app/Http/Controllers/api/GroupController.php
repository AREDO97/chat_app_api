<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Conversation_user;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Attachment;

class GroupController extends Controller
{
    // create group
    public function create(Request $request)
    {
        $user=$request->user();
        // validation
        $request->validate([
            'name'=>'required|string',
             'user_id' => 'nullable|array',
             'user_id.*' => 'integer|exists:users,id',
        ]);
        // create group
       $conversation = Conversation::create([
            'type'=>'group',
            'name'=>$request->name
        ]);
        // owner
       $mainUser = Conversation_user::create([
            'conversation_id'=>$conversation->id,
            'user_id'=>$user->id,
            'role'=>'owner'
        ]);
        // add other members to group
        $otherUser=$request->user_id;
        foreach ($otherUser as $other)
            {
               $otherUser = Conversation_user::create([
            'conversation_id'=>$conversation->id,
            'user_id'=>$other,
            'role'=>'member'
                    ]);
           
            }
            // response
            return response()->json([
                'message'=>'Group created successifully'
            ]
            );
    }
    // send message to group
    public function sendMessageToGroup(Request $request,Conversation $conversation)
    {

         $request->validate([
        'body' => ['required', 'string', 'max:5000'],
        'image' => ['nullable', 'file', 'image', 'max:10240'],
        'audio' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg', 'max:20480'],
    ]);
    // current user
        $user=$request->user();
        $allowedUser=$conversation->conversationUsers()->where('user_id',$user->id)
        ->exists();
        if (! $allowedUser)
            {
                abort(403,'Unauthorized action !!');
            }
            // send message
         $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $user->id,
        'body' => encrypt($request->body),
    ]);

  if ($request->hasFile('image')) {

    Attachment::createForMessage(
        $message,
        $request->file('image'),
        'chat/images'
    );
}
// audios
if ($request->hasFile('audio')) {

    Attachment::createForMessage(
        $message,
        $request->file('audio'),
        'chat/audios'
    );
}
// videos
if ($request->hasFile('video')) {

    Attachment::createForMessage(
        $message,
        $request->file('video'),
        'chat/videos'
    );
}

// document
if ($request->hasFile('document')) {

    Attachment::createForMessage(
        $message,
        $request->file('document'),
        'chat/documents'
    );
}
    // 8. Return response
    return response()->json([
        'message' => 'Message sent successfully',
        'data' => $message->load('attachments'),
    ], 201);
    }
    // delete group
   // delete group
public function destroy(Request $request, Conversation $conversation)
{
    // 1. Get authenticated user
    $user = $request->user();

    // 2. Check whether the user is the group owner
    $allowedUser = $conversation->conversationUsers()
        ->where('role', 'owner')
        ->where('user_id', $user->id)
        ->exists();

    // 3. Only owner can delete the group
    if (! $allowedUser) {
        abort(403, 'Unauthorized action !!');
    }

    // 4. Delete all messages belonging to the group
    $conversation->messages()->delete();

    // 5. Delete all group members
    $conversation->conversationUsers()->delete();

    // 6. Delete the group/conversation
    $conversation->delete();

    // 7. Return response
    return response()->json([
        'message' => 'Group deleted successfully'
    ]);
}
}
