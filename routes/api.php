<?php

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\Car_Controller;
use App\Http\Controllers\SlotController;



use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Zone_Controller;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Wallet_UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('ApiKey')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group([
    'middleware'=>['ApiKey'],
    'prefix'=>'/admin'
    ],function (){
Route::post('login',[AdminController::class,'loginAdmin']);


});

Route::group([
    'middleware'=>['ApiKey'],
    'prefix'=>'/user'
    ],function (){
Route::post('login',[UserController::class,'loginUser']);
Route::post('register',[UserController::class,'registerUser']);
Route::post('book',[BookController::class,'create_book']);
Route::post('Update_User_Password',[UserController::class,'Update_User_Password']);



});

Route::group([
    'middleware'=>['admin-Auth:admin','ApiKey'],
    'prefix'=>'/admin'

    ],function (){
    Route::get('check',[AdminController::class,'index']);
    Route::post('create_book_admin',[BookController::class,'create_book_admin']);
    Route::get('Get_All_Slot',[SlotController::class,'Get_All_Slot']);




});


Route::group([
    'middleware'=>['user-Auth:user','ApiKey'],
    'prefix'=>'/user'

    ],function (){
    Route::get('check',[UserController::class,'index']);
    Route::post('create_book_user_previous',[BookController::class,'create_book_user_previous']);
    Route::post('create_book_user_now',[BookController::class,'create_book_user_now']);
    Route::get('Get_All_Zone',[Zone_Controller::class,'Get_All_Zone']);

    Route::post('Create_Car',[Car_Controller::class,'Create_Car']);
    Route::get('Get_Cars_User',[Car_Controller::class,'Get_Cars_User']);
    Route::post('Delete_Car',[Car_Controller::class,'Delete_Car']);
    Route::post('Update_User_Phone',[UserController::class,'Update_User_Phone']);
    Route::post('Update_User_Name',[UserController::class,'Update_User_Name']);
    Route::get('Get_User',[UserController::class,'Get_User']);
    Route::get('Get_Amount',[Wallet_UserController::class,'Get_Amount']);
    Route::get('Get_Transaction',[TransactionController::class,'Get_Transaction']);

    Route::post('Get_Book',[BookController::class,'Get_Book']);






});
