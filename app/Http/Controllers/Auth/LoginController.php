<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:doctor')->except('logout');
    }
    public function showLoginForm()
    {
        return view('auth.login');
    }
//    public function loginDoctor(Request $request)
//    {
//    {
//        $this->validate($request, [
//            'email'   => 'required|email',
//            'password' => 'required|min:6'
//        ]);
//        if (Auth::guard('doctor')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
//            return redirect()->intended('/doctor/dashboard');
//        }
//        return back()->withInput($request->only('email', 'remember'));
//    }
    public function loginDoctor(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $doctor = Doctor::where('email', $fields['email'])->first();

        if ($doctor) {

            if (Hash::check($fields['password'], $doctor->password)) {
                echo "here2";
                die();
                //return "tvre";
            }
            echo "here3";
            die();
        }

    }}
