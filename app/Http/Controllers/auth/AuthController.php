<?php

namespace App\Http\Controllers\auth;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Profile;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Password;
class AuthController extends Controller
{
    // create user
public function register(Request $request)
{
    //  Validation
    $request->validate([
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'min:6'], 
    ]);

    // Insert User
    $user = User::create([
        'name'     => $request->name,
        'email'    => strtolower(trim($request->email)),
        'password' => Hash::make($request->password)
    ]);

    // Token 
    $token = $user->createToken('auth-token');
    // log action $userId,$action,$body
  $logs = AuditLog::logAction(
        $request,
        $user->id,
        'Account Creation',
         [
        'message' => 'User created an account successfully',
        'name' => $user->name,
        'email' => $user->email,
    ]
    );
    // response
    return response()->json([
        'message' => 'Account created successfully',
        'user'    => $user,
        'token'   => $token,
        'logs'    =>$logs,
    ], 201);
}
    // login
    public function login(Request $request)
{
    // 1. Validation
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'remembere'=>'sometimes|boolean'
    ]);

    // 2. Fetch user from DB
// 1. Fetch user by email only
$user = User::where('email', $request->email)->first();

// 2. Check if user exists
if (!$user) {
    // log action
      $logs = AuditLog::logAction(
        $request,
        null,
        'Login Failure',
         [
        'message' => 'User failed to log into account',
    ]
    );
    return response()->json([
        'message' => 'Invalid credentials',
        'logs'=>$logs
    ], 401);
    
}


    // 3. Check existence AND verify password using OR (||)
    if (!$user || !Hash::check($request->password, $user->password)) {
        // log action
     AuditLog::logAction(
            $request,
            $user->id,
            'Login Failure',
            [
                'message' => 'Login failed - incorrect password',
                'email' => $user->email,
            ]
        );
        return response()->json([
            'message' => 'Invalid email or password'
        ], 401);
    }
    // generate remember me token
    $expiresAt=$request->boolean('remember')
    ?  now()->addDays(30)
    : now()->addMinutes(30);

    // 4. Generate plain text token
    $token = $user->createToken('auth-token'
    ,
    [],
    $expiresAt );
   // log action
     $logs = AuditLog::logAction(
        $request,
        $user->id,
        'Account login',
         [
        'message' => 'User logged into account successfully',
        'name' => $user->name,
        'email' => $user->email,
    ]
    );
    //response
    return response()->json([
        'token' => $token,
        'user'  => $user,
        'logs'  =>$logs
    ], 200);
}
    // logout logic
    public function logout(Request $request)
{
    $user=$request->user();
    // log action
      $logs = AuditLog::logAction(
        $request,
        $user->id,
        'Account Logout',
         [
        'message' => 'User logged out of account ',
        'name' => $user->name,
        'email' => $user->email,
    ]
    );
    // Revoke the specific token that was used to authenticate this request
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged out successfully',
        'logs'=>$logs
    ], 200);
}

// get the current logged in user
public function getMe(Request $request)
{
    $user=$request->user();
    // response
    return response()->json($user);
}

}




