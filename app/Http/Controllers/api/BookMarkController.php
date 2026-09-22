<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageBookmark;
use Illuminate\Http\Request;

class BookMarkController extends Controller
{
    // post book mark on message
    public function create(Request $request,Message $message)
    {
         $user=$request->user();
        $allowedUsers=$message->conversation->conversationUsers()->where('user_id',$user->id)->exists();
        if (! $allowedUsers)
            {
                abort(403,'Unauthorized action');
            }
            // create book mark
            $bookMark = MessageBookmark::create([
                'user_id'=>$user->id,
                'message_id'=>$message->id
            ]);
            // response
            return response()->json([
                'book_mark'=>$bookMark,
                'message'=>$message
            ]);
    }
    // bookmarked messages for a user
    public function bookmarkedMessages(Request $request)
    {
         $user=$request->user();
         $userBookMarks=$user->bookmarks()
         ->where('is_deleted',false)->with('message')->get();
         //response
         return response()->json([
            'book_marks'=>$userBookMarks
         ]);
    }
    // delete bookmark
    public function destroy(Request $request,MessageBookmark $bookmark)
    {
        $user=$request->user();
        if($user->id !== $bookmark->user_id)
            {
                abort(403,'Unauthorized action');
            }
            $bookmark->update([
                'is_deleted'=>true
            ]);
            // response
            return response()->json([
                'message'=>'Bookmark deleted successifully',
                'bookmark'=>$bookmark
            ]);
    }
}
