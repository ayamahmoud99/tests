<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::group(['prefix'=>'doctor'],function (){
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('LoginForm');
    Route::post('/login', [LoginController::class, 'loginDoctor'])->name('loginPost');
    Route::get('/register', [RegisterController::class, 'showDoctorRegisterForm'])->name('RegisterForm');
    Route::post('/register', [RegisterController::class, 'createDoctor'])->name('registerPost');
});


Route::get('/doctor/dashboard',function(){
    return view('doctor');
})->middleware('auth:doctor');
