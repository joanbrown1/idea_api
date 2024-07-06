<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IdeaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('welcome', [UserController::class, 'index']);

Route::group(['prefix' => 'user'], function() {
    Route::post('login', [UserController::class, 'login']);
    Route::post('register', [UserController::class, 'register']);
    Route::get('all', [UserController::class, 'allUsers'])->name('user.all');
    Route::get('email/{email}', [UserController::class, 'UsersByEmail'])->name('user.byEmail');
    Route::get('id/{id}', [UserController::class, 'UsersById'])->name('user.byId');
    Route::get('department/{department}', [UserController::class, 'UsersByDepartment'])->name('user.byDepartment');
    Route::get('zone/{zone}', [UserController::class, 'UsersByZone'])->name('user.byZone');
    Route::put('update/{id}', [UserController::class, 'update'])->name('user.update');
});

Route::group(['prefix' => 'admin'], function() {
    Route::post('login', [AdminController::class, 'login']);
    Route::post('register', [AdminController::class, 'register']);
    Route::get('all', [AdminController::class, 'allAdmins'])->name('admin.all');
    Route::get('email/{email}', [AdminController::class, 'AdminsByEmail'])->name('admin.byEmail');
    Route::get('id/{id}', [AdminController::class, 'AdminsById'])->name('admin.byId');
    Route::get('department/{department}', [AdminController::class, 'AdminsByDepartment'])->name('admin.byDepartment');
    Route::get('zone/{zone}', [AdminController::class, 'AdminsByZone'])->name('admin.byZone');
    Route::put('update/{id}', [AdminController::class, 'update'])->name('admin.update');
});



Route::group(['prefix' => 'idea'], function() {
    Route::get('all', [IdeaController::class, 'allIdeas'])->name('idea.all');
    Route::post('create', [IdeaController::class, 'create'])->name('idea.create');
    Route::get('user/{user_id}', [IdeaController::class, 'IdeasByUserId'])->name('idea.byUserId');
    Route::get('id/{id}', [IdeaController::class, 'IdeasById'])->name('idea.byId');
    Route::get('department/{department}', [IdeaController::class, 'IdeasByDepartment'])->name('idea.byDepartment');
    Route::get('zone/{zone}', [IdeaController::class, 'IdeasByZone'])->name('idea.byZone');
    Route::get('category/{category}', [IdeaController::class, 'IdeasByCategory'])->name('idea.byCategory');
    Route::get('division/{division}', [IdeaController::class, 'IdeasByDivision'])->name('idea.byDivision');
    Route::put('idea/update/{id}', [IdeaController::class, 'update'])->name('idea.update');
});
