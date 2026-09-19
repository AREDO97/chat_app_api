<?php

namespace App\Http\Controllers\api;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AuditLog;

class UserController extends Controller
{
  // all users
  public function index()
  {
    $users=User::latest()->paginate(10);
    return response()->json($users);
  }
  //access one user
  public function oneUser(User $user)
  {
    return response()->json($user);
  }
// update user info mention
public function update(Request $request,User $user)
{
    $allowedUser=$request->user();
    if($allowedUser->id !== $user->id && $user->role !== "super_admin"){
        abort(403,'Unauthorised action');
    }
    //validation
    $request->validate([
        'name'=>'string|max:45',
        'email'=>'email',
        'phone'=>'string|max:15'
    ]);
    $update=$user->update([
        'name'=>$request->name ?? $user->name,
        'email'=>$request->email ?? $user->email,
        'role'=>$request->role ?? $user->role,
        'phone'=>$request->phone ?? $user->phone
    ]);

    return [
        'message'=>'user profile updated',
        'profile'=>$user
    ];
}
// soft delete
public function softDelete(Request $request,User $user)
{
     $admin=$request->user();
    if($admin->role !== 'admin' && $admin->role !== 'super_admin'){
        abort(403,'Unauthorised action');
    }
    $user->update([
        'is_active'=>false
    ]);
    //$user->save();

// log out event
  /*  AuditLog::Log(
        $admin->id,
        'User Suspension',
        $admin->name.' suspended user '.$user->name
    );*/
//response
    return [
        'message'=>'User suspended successifuuly',
        'user'=>$user
    ];
}

// unsuspend user
public function unsuspend(Request $request,User $user)
{
    // enforce admins
    $admin=$request->user();
    if($admin->role !== 'admin' && $admin->role !== 'super_admin'){
        abort(403,'Unauthorised action');
    }
    $user->update([
        'is_active'=>true
    ]);

// log out event
    /*   AuditLog::Log(
     $admin->id,
    'User Unsuspension',
    $admin->name.' unsuspended '.$user->name
);*/

    return response()->json([
        'message'=>'User unsuspend successiful',
        'user'=>$user
    ]);

}


// view suspended users
public function viewSuspended()
{
    $suspended=User::where('is_active',true)->latest()->get();
    return response()->json($suspended);
}

// change role to admin
public function makeAdmin(Request $request,User $user)
{
    $admin=$request->user();
    if($admin->role !== 'admin' && $admin->role !== 'super_admin'){
        abort(403,'Unauthorised action');
    }
    $user->update([
        'role'=>'admin'
    ]); 
    $role=$user->role;
     
    // log out event
       /* AuditLog::Log(
            $admin->id,
            'Admin Creation',
            $admin->name.' made '.$user->name.' an admin'
        );*/

    return [
        'message'=>'user role updated to admin',
        'role'=>$role
    ];
}
// demote role to user

public function demoteAdmin(Request $request,User $user)
{
     $admin=$request->user();
    if($admin->role !== 'admin' && $admin->role !== 'super_admin'){
        abort(403,'Unauthorised');
    }
    $user->update([
        'role'=>'user'
    ]); 


    $role=$user->role;

    // log out event
       /* AuditLog::Log(
            $admin->id,
            'Admin Demotion',
            $admin->name.' demoted '.$user->name.' to a user'
        );*/
// response

    return [
        'message'=>'Admin demoted to normal user ',
        'role'=>$role
    ];
}

// user activity
    public function userActivity(Request $request)
    {
        $user=$request->user();
        $logs=$user->logs;
        // response
        return response()->json([
            'user'=>$user,
            'logs'=>$logs
        ]);
    }

    // user stats summary
    public function userSummary(Request $request)
    {
         $admin=$request->user();
    if($admin->role !== 'admin' && $admin->role !== 'super_admin'){
        abort(403,'Unauthorised action');
    }
    // total number of users
    $total_users=User::all()->count();
    // users today
    $total_users_today=User::whereDate('created_at',today())
    ->count();
    // users created yesterday
    $totalYesterday=User::whereDate('created_at',today()->subDay())->count();
    // total users this month
    $totalMonthly=User::whereBetween('created_at',[
        today()->startOfMonth(),
        today()->endOfMonth()
    ])->count();
    // total last month
     $totalLastMonth=User::whereBetween('created_at',[
        today()->subMonth()->startOfMonth(),
        today()->subMonth()->endOfMonth()
    ])->count();

    // response
    return response()->json([
        'total_users'=>$total_users,
        'today_accounts_created'=>$total_users_today,
        'total_yesterday'=>$totalYesterday,
        'total_current_month'=>$totalMonthly,
        'total_last_month'=>$totalLastMonth
    ]);

    }
}
