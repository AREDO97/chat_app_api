<?php

use App\Http\Controllers\api\BookMarkController;
use App\Http\Controllers\api\ConversationController;
use App\Http\Controllers\api\GroupController;
use App\Http\Controllers\api\MessageReactionController;
use App\Http\Controllers\api\MessagesController;
use App\Http\Controllers\api\PinnedController;
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
    // message attachments
    Route::get('/messages/{message}/attachments',[MessagesController::class,'messageAttachments']);
});

// user conversations
Route::middleware('auth:sanctum')->group(function () {
// all user conversations
Route::get('/conversations',[ConversationController::class,'index']);
// conversation
Route::get('/conversations/{conversation}',[ConversationController::class,'show']);

});

// message reaction
Route::middleware('auth:sanctum')->group(function () {
// message reaction
Route::post('/messages/{message}/reaction',[MessageReactionController::class,'reactToMessage']);

});

// book mark management
Route::middleware('auth:sanctum')->group(function () {
// bookmarkedMessages
Route::get('/bookmarks',[BookMarkController::class,'bookmarkedMessages']);
// create book mark
Route::post('/bookmark/{message}/create',[BookMarkController::class,'create']);
// delete book mark
Route::delete('/bookmark/{bookmark}/delete',[BookMarkController::class,'destroy']);
});

// pinned message management
Route::middleware('auth:sanctum')->group(function () {
// pin a message
Route::post('/pin/{message}/create',[PinnedController::class,'create']);
// get pinned 
Route::get('/pinned_messages',[PinnedController::class,'index']);
});

// group management controller
Route::middleware('auth:sanctum')->group(function () {
 // create group
 Route::post('/group/create',[GroupController::class,'create']);
 // send message to group
 Route::post('/group/{conversation}/message',[GroupController::class,'sendMessageToGroup']);
 // delete group
 Route::delete('/group/{conversation}/delete',[GroupController::class,'destroy']);
});