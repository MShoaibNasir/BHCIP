<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Authentication;
use App\Http\Controllers\API\ForgetPasswordController;
use App\Http\Controllers\API\ResourcesController;
use App\Http\Controllers\API\ContentController;
use App\Http\Controllers\API\LevelController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\OTPVerification;

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

// web.php
Route::get('get_topic_by_level/{category_id}/{level_id}', [ForgetPasswordController::class, 'gettopicbylevelIDTest']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [Authentication::class, 'login']);
    Route::post('/register', [Authentication::class, 'register']);
    Route::post('/user_logout', [Authentication::class, 'logout']);
    Route::post('/changePassword', [Authentication::class, 'changePassword']);
    Route::post('/updateUser', [Authentication::class, 'userUpdate']);

});

/************************************************
*              OTP Verification 
*************************************************/
Route::post('/sendOTPCode', [OTPVerification::class, 'send_otp_code']);
Route::post('/CodeOTPCheck', [OTPVerification::class, 'CodeOTPCheck']);

/************************************************
*               Forget Password
*************************************************/
Route::post('/sendCode', [ForgetPasswordController::class, 'send_code']);
Route::post('/CodeCheck', [ForgetPasswordController::class, 'CodeCheck']);
Route::post('/resetPassword', [ForgetPasswordController::class, 'resetPassword']);
Route::get('/user/delete/{id}', [Authentication::class, 'deleteUser']);

/************************************************
*               Get Resources  
*************************************************/

Route::get('/getDistrict/{id?}', [ResourcesController::class, 'get_district']);
Route::get('/getFacility/{district_id?}', [ResourcesController::class, 'get_facility']);
Route::get('/getCategory/{id?}', [ResourcesController::class, 'get_category']);
Route::post('/addLog/', [ResourcesController::class, 'add_user_activity_log']);


/************************************************
*               Get Level   
*************************************************/

Route::get('/getLevels/{id?}', [LevelController::class, 'get_levels']);
Route::get('/getTopicByLevelID/{id}/{cat_id}/{user_id?}', [LevelController::class, 'get_topic_by_levelID']);

Route::get('/getTopicDetail/{id}', [LevelController::class, 'get_topic_detail']);
Route::get('/getUnlockLevel/{user_id}', [LevelController::class, 'get_unlock_level']);


/************************************************
*               Get Quiz    
*************************************************/

Route::post('/get_quiz', [QuizController::class, 'get_quiz']);
Route::post('/filterReportsexample', [QuizController::class, 'filterReportsexample']);
Route::post('/quiz/saveResponse', [QuizController::class, 'save_quiz_response']);
Route::post('/quiz/saveResult', [QuizController::class, 'save_quiz_result']);
Route::post('/quiz/dummy_save_quiz_result', [QuizController::class, 'dummy_save_quiz_result']);
Route::get('/userInfo/{id}', [QuizController::class, 'userInfo']);
Route::post('/getResult', [QuizController::class, 'get_quiz_result']);
Route::post('/levelResult', [QuizController::class, 'get_levelwise_result']);
Route::post('/getquizResponse', [QuizController::class, 'get_quiz_response']);



/********************************************************
* Get Content of privacy policy and Terms and Conditions     
*********************************************************/
Route::get('/privacy/policy', [ContentController::class, 'privacy_policy']);
Route::get('/terms/condition', [ContentController::class, 'terms_and__condition']);



/********************************************************
*                     Feedback
*********************************************************/
Route::post('/feedback', [QuizController::class, 'feedback']);
Route::post('/uploadImage/{id}', [Authentication::class, 'uploadImage']);
