<?php

namespace App\Http\Controllers\API\Patient;

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

use App\Models\Patient;
use App\Models\Doctor;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:patient')->except('logout');
    }

    //register function handles registration process
    public function register() {
        try{
            return DB::transaction(function() {
                $fields = request()->validate([
                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                    'email' => 'required|email|unique:patients,email',
                    'password' => 'required|string|confirmed',
                    'professor_code' => 'required|string|exists:doctors,code',
                    'pathology' => 'nullable|string',
                    'symptoms' => 'nullable|string',
                ]);

                // find the assigned professor
                $doctor = Doctor::whereCode(request()->professor_code)->first();
                $doctor_id = $doctor ? $doctor->id : null;

                $patient = Patient::create([
                    'first_name' => request()->first_name,
                    'last_name' => request()->last_name,
                    'email' => request()->email,
                    'password' => request()->password,
                    'pathology' => request()->pathology,
                    'symptoms' => request()->symptoms,
                    'doctor_id' => $doctor_id,
                ]);

                $token = $patient->createToken('myapptoken')->plainTextToken;

                $response = [
                    'id' => $patient->id,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'email' => $patient->email,
                    'pathology' => $patient->pathology,
                    'symptoms' => $patient->symptoms,
                    'doctor_id' => $patient->doctor_id,
                    'user_type' => 'patient',
                    'token' => $token
                ];

                return response($response, 201);
            });
        }
        catch(Exception $ex){
            info($ex->getMessage().'. File: '.$ex->getFile().' Line: '.$ex->getLine());
            return response($ex->getMessage(), 500);
        }
    }
}
