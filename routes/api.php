<?php

use App\Http\Controllers\api\MessagesController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// register user
Route::post('/register',[AuthController::class,'register'])->middleware('throttle:3,1')
->name('register');
// login user
Route::post('/login',[AuthController::class,'login'])->middleware('throttle:3,1')
->name('login');
// log out endpoint
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum')
->name('logout');
// get current logged in user
Route::get('/me',[AuthController::class,'getMe'])->middleware('auth:sanctum')
->name('current user');
// user management
// all users
Route::get('/users',[UserController::class,'index'])
->middleware('auth:sanctum')->name('view users');
Route::get('/userActivity',[UserController::class,'userActivity'])
->middleware('auth:sanctum')->name('user activity');
Route::get('/user/{user}',[UserController::class,'oneUser'])
->middleware('auth:sanctum')->name('view single user');
// update
Route::patch('/users/update/{user}',[UserController::class,'update'])
->middleware('auth:sanctum')->name('update user info');
// only admin delete and update role
Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function () {
    // view suspended
    Route::patch('/users/suspend/{user}', [UserController::class, 'softDelete'])->name('suspend user');
    Route::patch('/users/create_admin/{user}', [UserController::class, 'makeAdmin'])->name('make admin');
    Route::patch('/users/demote_admin/{user}', [UserController::class, 'demoteAdmin'])->name('demote admin');
    //viewSuspended
    Route::get('/users/suspended', [UserController::class, 'viewSuspended'])->name('suspended users');
    // unsuspend user
     Route::patch('/users/unsuspend/{user}', [UserController::class, 'unsuspend'])->name('unsuspend user');
    // users stats summary
    Route::get('/users_stats/summary',[UserController::class,'userSummary'])
    ->name('user_stats summary');
});

Route::middleware('auth:sanctum')->group(function () {
    // send message to another user
    Route::post('/messages', [MessagesController::class, 'store'])
    ->name('send message');
     // massDeleteMessages
    Route::delete('/messages/delete',[MessagesController::class,'massDeleteMessages'])
    ->name('mass_delete message');
    // conversationMessages
    Route::get('/conversation/{conversation}/messages',[MessagesController::class,'conversationMessages'])
    ->name('conversation messages');
    // delete messages deleteMessage
    Route::delete('/messages/{message}/delete',[MessagesController::class,'deleteMessage'])
    ->name('delete message');
    // replyMessage
    Route::post('/messages/{message}/reply',[MessagesController::class,'replyMessage'])
    ->name('reply message');
    // edit message editMessage
    Route::put('/messages/{message}/edit',[MessagesController::class,'editMessage']);
});