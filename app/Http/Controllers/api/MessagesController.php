<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Conversation_user;
use App\Models\User;
use App\Models\Attachment;
use App\Models\Message;

class MessagesController extends Controller
{
    // send message
    public function store(Request $request)
{
    // 1. Validate
  $request->validate([
        'receiver_id'  => ['required', 'exists:users,id'],
        'body'         => ['nullable', 'string', 'max:5000'],
        'image'        => ['nullable', 'file', 'image', 'max:10240'],
        'audio'        => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg,webm,mp4', 'max:20480'],
        'audio_record' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg,webm,mp4', 'max:20480'],
    ]);

    // 2. Authenticated user
    $sender = $request->user();

    // 3. Receiver
    $receiver = User::findOrFail($request->receiver_id);

    // 4. Prevent self messaging
    if ($sender->id === $receiver->id) {
        return response()->json([
            'message' => 'You cannot send a message to yourself.'
        ], 422);
    }

    // 5. Find existing conversation
    $conversation = Conversation::whereHas('conversationUsers', function ($query) use ($sender) {
        $query->where('user_id', $sender->id);
    })
    ->whereHas('conversationUsers', function ($query) use ($receiver) {
        $query->where('user_id', $receiver->id);
    })
    ->withCount('conversationUsers')
    ->having('conversation_users_count', 2)
    ->first();

    // 6. Create conversation if it doesn't exist
    if (!$conversation) {

        $conversation = Conversation::create();

        Conversation_user::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
        ]);

        Conversation_user::create([
            'conversation_id' => $conversation->id,
            'user_id' => $receiver->id,
        ]);
    }


    $path=null;
 // 7. Store audio file
 if($request->hasFile('audio_record'))
    {
$path = $request->file('audio_record')
        ->store('messages', 'public');

          // 8. Create message
    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $sender->id,
        'type' => 'audio',
        'body'=>$request->body,
        'media_path' => $path,
    ]);
    }
    else
    {
  // 7. Create message
    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $sender->id,
        'body' => encrypt($request->body),
    ]);
    }
  


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

    // conversation messages
    public function conversationMessages(Conversation $conversation,Request $request)
    {
        // current user
        $user=$request->user();
        $coversationUsers = $conversation->conversationUsers;
        $isParticipant = false ;
        foreach($coversationUsers as $realUser)
            {
                if($realUser->user_id == $user->id)
                    {
                        $isParticipant = true ;
                        break;
                    }
            }
            if(! $isParticipant)
                {
                    abort(403,'Unauthorized action');
                }
        $messages=$conversation->messages;
        foreach($messages as $message)
            {
                $message->body=decrypt($message->body);
            }
        // response
        $conversation->messages()
    ->where('sender_id', '!=', $user->id)
    ->whereNull('read_at')
    ->update([
        'read_at' => now(),
    ]);
        return response()->json([
            'messages'=>$messages,
            'conversation_users'=>$coversationUsers
        ]);
    }
    // delete message
    public function deleteMessage(Message $message,Request $request)
    {
        $currentUser=$request->user();
        $users=$message->conversation->conversationUsers;
        $isParticipant = false ;
        foreach($users as $user)
            {
                if($user->id == $currentUser->id)
                    {
                        $isParticipant = true ;
                        break;
                    }
            }
            if(! $isParticipant)
                {
                    abort(403,'Unauthorised action');
                }
         // delete message
            $message->update([
                'is_deleted'=>true
            ]);
        // response
        return response()->json([
            'message'=>'message deleted successifully',
            'message_deleted'=>$message
        ]);
    }
    // mass delete messages
    public function massDeleteMessages(Request $request)
    {
      
        $currentUser=$request->user();
         // obtain message ids
        $data = $request->validate([
    'message_id' => 'required|array',
    'message_id.*' => 'integer|exists:messages,id',
                                ]);
        foreach($data['message_id'] as $id)
            {
                $message=Message::findOrFail($id);
                // message users
                $users=$message->conversation->conversationUsers;
                $isParticipant = false ;
                // loop through users
                 foreach($users as $user)
            {
                if($user->id == $currentUser->id)
                    {
                        $isParticipant = true ;
                        break;
                    }
            }

              if(! $isParticipant)
                {
                    abort(403,'Unauthorised action');
                }
                $message->update([
                    'is_deleted'=> true
                ]);
            }
        
            // response
            return response()->json([
                'message'=>"Messages deleted successifully",
               // 'deleted_messages'=>$message
            ]);
    }
    // reply a specific message
    public function replyMessage(Request $request,Message $message)
    {
        /*
          'conversation_id',
        'sender_id',
        'body',* */
        $currentUser=$request->user();
        $authorizedUsers=$message->conversation->conversationUsers;
        // conversation_id
        $conversation = $message->conversation ;
        $isAllowed = false ;
        foreach($authorizedUsers as $allowedUsers)
            {
                if ($allowedUsers->id == $currentUser->id)
                    {
                        $isAllowed = true ;
                    }
            }
            if (! $isAllowed)
                {
                    abort(403,'Unauthorized action');
                }
       // validate
       $request->validate([
        'body'=>'required'
       ]);
       //  Create message
    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $currentUser->id,
        'body' => encrypt($request->body),
    ]);
        // response
        return response()->json([
            'users'=>$authorizedUsers,
            'message'=>$message
        ]);
    }
// edit message
    public function editMessage(Request $request,Message $message)
    {
           $currentUser=$request->user();
        $authorizedUsers=$message->conversation->conversationUsers;
        // conversation_id
        $conversation = $message->conversation ;
        $isAllowed = false ;
        foreach($authorizedUsers as $allowedUsers)
            {
                if ($allowedUsers->id == $message->sender_id)
                    {
                        $isAllowed = true ;
                    }
            }
            if (! $isAllowed)
                {
                    abort(403,'Unauthorized action');
                }
        $newBody = encrypt($request->body) ;
       // update message body
       $message->update([
        'body'=> $newBody
       ]);
       // response
       return response()->json([
        'message'=>'message edited ',
        'message_new'=>$message,
        'new_message_body'=>decrypt($newBody)
       ]);

    }

    // message attachments
    public function messageAttachments(Request $request,Message $message)
    {
        $attachments=$message->attachments;
        // response
        return response()->json([
            'message'=>$message,
            'message_attachments'=>$attachments
        ]);
    }
    // send audio message
// send audio message
public function audio(Request $request)
{
    // 1. Validate
    $request->validate([
        'receiver_id' => ['required', 'exists:users,id'],
        'audio' => ['required', 'file', 'mimes:mp3,wav,m4a,ogg', 'max:20480'],
    ]);

    // 2. Authenticated user
    $sender = $request->user();

    // 3. Receiver
    $receiver = User::findOrFail($request->receiver_id);

    // 4. Prevent self messaging
    if ($sender->id === $receiver->id) {
        return response()->json([
            'message' => 'You cannot send a message to yourself.'
        ], 422);
    }

    // 5. Find existing conversation
    $conversation = Conversation::whereHas('conversationUsers', function ($query) use ($sender) {
        $query->where('user_id', $sender->id);
    })
    ->whereHas('conversationUsers', function ($query) use ($receiver) {
        $query->where('user_id', $receiver->id);
    })
    ->withCount('conversationUsers')
    ->having('conversation_users_count', 2)
    ->first();

    // 6. Create conversation if it doesn't exist
    if (!$conversation) {

        $conversation = Conversation::create();

        Conversation_user::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
        ]);

        Conversation_user::create([
            'conversation_id' => $conversation->id,
            'user_id' => $receiver->id,
        ]);
    }

    // 7. Store audio file
    $path = $request->file('audio')
        ->store('messages', 'public');

    // 8. Create message
    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $sender->id,
        'type' => 'audio',
        'body'=>$request->body,
        'media_path' => $path,
    ]);

    // 9. Return response
    return response()->json([
        'message' => 'Audio message sent successfully',
        'data' => $message
    ], 201);
}
}
