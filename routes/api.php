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
use App\Http\Controllers\Wallet_AdminController;


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
    'prefix'=>'/accountable'
    ],function (){
Route::post('Add_Zone',[Zone_Controller::class,'Add_Zone']);
Route::post('Delete_Zone',[Zone_Controller::class,'Delete_Zone']);
Route::post('Add_Slots',[SlotController::class,'Add_Slots']);
Route::post('Delete_Slot',[SlotController::class,'Delete_Slot']);
Route::post('Add_Slot',[SlotController::class,'Add_Slot']);






});
Route::group([
    'middleware'=>['ApiKey'],
    'prefix'=>'/admin'
    ],function (){
Route::post('login',[AdminController::class,'loginAdmin']);
Route::post('register',[AdminController::class,'registerAdmin']);



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
    Route::post('create_outside_admin',[BookController::class,'create_outside_admin']);
    Route::post('create_violation_admin',[BookController::class,'create_violation_admin']);

    Route::get('Get_All_Slot',[SlotController::class,'Get_All_Slot']);
    Route::post('End_Booking',[BookController::class,'End_Booking']);
    Route::post('End_Booking_All',[BookController::class,'End_Booking_All']);
    Route::post('Deposit',[Wallet_AdminController::class,'Deposit']);

    Route::post('Get_Book_slot',[BookController::class,'Get_Book_slot']);
    Route::get('type_cost',[BookController::class,'type_cost']);
    Route::post('update_booking_merge',[BookController::class,'update_booking_merge']);






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
    Route::post('Extend_ParkingTime',[BookController::class,'Extend_ParkingTime']);
    Route::post('End_Booking',[BookController::class,'End_Booking']);
    Route::get('type_cost',[BookController::class,'type_cost']);









});
