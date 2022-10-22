<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\AuthController;


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

/*
|---------------------------------------------------------------
| --------- COMMEN ROUTES BETWEEN PATIENT & DOCTOR -------------
|---------------------------------------------------------------
*/



// Public routes register,login,and forget_password
Route::namespace('API')->group(function(){
    Route::post('/get-user', 'AuthController@getUser');
    Route::post('/login', 'AuthController@login'); //

    Route::post('/forgot-password',  'AuthController@ForgotPassword');//1
    Route::post('/code-check', 'AuthController@CodeCheck');//2
    Route::post('/reset-password', 'AuthController@reset');//3

    // Protected routes logout,change_password
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('/logout', 'AuthController@logout');
        Route::post('/change_password', 'AuthController@change_password');
    });

    // get valid doctores's codes
    // Route::get('/get-codes', 'AuthController@get_codes');


    // test controller
    Route::get('/test', 'TestController@test');
});


/*
|---------------------------------------------------------------
| ----------------------- PATIENTS ROUTES ----------------------
|---------------------------------------------------------------
*/
Route::name('patient.')->namespace('API\Patient')->prefix('patient')->group(function(){

    // auth routes
    Route::post('/register','RegisterController@register');

    // authenticated routs
    Route::post('/edit', 'PatientController@update_profile');
    // Route::get('/get-professor-of-patient/{patient_id}', 'PatientController@getDoctor');

});

/*
|---------------------------------------------------------------
| ----------------------- DOCTORS ROUTES -----------------------
|---------------------------------------------------------------
*/
Route::name('doctor.')->namespace('API\Doctor')->prefix('doctor')->group(function(){

    // auth routes
    Route::post('/register','RegisterController@register');

});

