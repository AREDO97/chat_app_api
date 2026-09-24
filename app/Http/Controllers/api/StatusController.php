<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\StatusViews;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    // post status
    public function create(Request $request)
    {
        $user=$request->user();
        // validation
        $request->validate([
            'content'=>'required',
            'media'=>'nullable'
        ]);

        // file path
        $media_path=null;
        if ($request->hasFile('media'))
            {
                $media_path=$request->file('media')->store('status_media','public');

                 // create status
        $status = Status::create([
            'content'=>$request->content,
            'user_id'=>$user->id,
            'media_path'=>$media_path,
            'expires_at'=>now()->addHours(24)
        ]);
            }
            else
            {
        // create status
        $status = Status::create([
            'content'=>$request->content,
            'user_id'=>$user->id,
            'expires_at'=>now()->addHours(24)
        ]);
            }

        // response
        return response()->json([
            'message'=>'status updated ',
            'status' =>$status
        ]);
    }
    // user status
    public function index(Request $request)
    {
         $user=$request->user();
        $statuses=$user->statuses()->where('expires_at','>',now())->latest()->get();
        return response()->json([
            'user'=>$user,
            'status'=>$statuses
        ]);
    }
    // update status
    public function update(Request $request,Status $status)
    {
          $user=$request->user();
          if ($user->id !== $status->user_id)
            {
                abort(403,'Unauthorized');
            }
            // update status
            $status->update([
                'content'=>$request->content
            ]);
            // response
            return response()->json([
                'message'=>'Status updated successifully',
                'status'=>$status
            ]);
    }
    // delete status
      public function destroy(Request $request,Status $status)
    {
          $user=$request->user();
          if ($user->id !== $status->user_id)
            {
                abort(403,'Unauthorized');
            }
            // update status
            $status->delete();
            // response
            return response()->json([
                'message'=>'Status deleted',
            ]);
    }
    // view user status
    public function viewStatus(Request $request,Status $status)
    {
        $user=$request->user();
        if($status->expires_at <= now())
            {
                abort(405,'status unavailable');
            }
            // create view
             $view = StatusViews::firstOrCreate(
        [
            'status_id' => $status->id,
            'user_id' => $user->id,
        ]
    );
            $status->load('user');
            // response
            return response()->json([
                'status'=>$status,
                'view_count'=>$status->views()->count(),
            ]);
            
    }
    // all active status
    public function statusAll(Request $request)
    {
         $user=$request->user();
        $statuses=Status::where('expires_at','>',now())
        ->latest()->get();
        return response()->json([
             'status'=>$statuses->load('user')
        ]);
    }
}
