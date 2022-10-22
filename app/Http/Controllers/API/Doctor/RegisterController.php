<?php

namespace App\Http\Controllers\API\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use DB;
use Exception;

// password
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password as RulesPassword;
use App\Models\ResetCodePassword;
use App\Mail\SendCodeResetPassword;

use App\Models\Doctor;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:doctor')->except('logout');
    }

    //register function handles registration process
    public function register() {
        try{
            return DB::transaction(function() {
                $fields = request()->validate([
                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                    'email' => 'required|email|unique:doctors,email',
                    'password' => 'required|string|confirmed',
                    'specialty' => 'string',
                ]);

                $doctor = Doctor::create([
                    'first_name' => $fields['first_name'],
                    'last_name' => $fields['last_name'],
                    'email' => $fields['email'],
                    'password' => $fields['password'],
                    'specialty' => $fields['specialty'],
                ]);

                $code = $doctor->id.Str::random(10);
                $doctor->update(['code' => $code]);

                $token = $doctor->createToken('myapptoken')->plainTextToken;

                $response = [
                    'id' => $doctor->id,
                    'first_name' => $doctor->first_name,
                    'last_name' => $doctor->last_name,
                    'email' => $doctor->email,
                    'code' => $doctor->code,
                    'specialty' => $doctor->specialty,
                    'user_type' => 'professor',
                    'token' => $token
                ];

                return response($response, 201);
            });
        }
        catch(Exception $ex){
            info($ex->getMessage().'. File: '.$ex->getFile().' Line: '.$ex->getLine());
            return response()->json($ex->getMessage(), 500);
        }
    }

}
