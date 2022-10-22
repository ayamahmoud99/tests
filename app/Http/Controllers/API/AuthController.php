<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password as RulesPassword;
use DB;
use Exception;

use App\Models\ResetCodePassword;
use App\Mail\SendCodeResetPassword;
use Illuminate\Support\Facades\Mail;

//models
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;

class AuthController extends Controller
{

    // login function handles login process
    public function login(Request $request) {
        try{
            $fields = $request->validate([
                'email' => 'required|string',
                'password' => 'required|string'
            ]);

            // Check email

            $doctor = Doctor::where('email', $fields['email'])->first();
            $patient = Patient::where('email', $fields['email'])->first();

            // doctor account
            if($doctor){
                // Check password
                if(!$doctor || !Hash::check($fields['password'], $doctor->password)) {
                    return response([
                        'message' => 'Bad creds'
                    ], 401);
                }

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
            }

            else if($patient){
                if(!$patient || !Hash::check($fields['password'], $patient->password)) {
                    return response([
                        'message' => 'Bad creds'
                    ], 401);
                }

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
            }

            return response([
                'message' => 'Bad creds'
            ], 401);
        }
        catch(Exception $ex){
            info($ex->getMessage().'. File: '.$ex->getFile().' Line: '.$ex->getLine());
            return response($ex->getMessage(), 500);
        }
    }

    // return user by token
    public function getUser() {
        try{
            $post_data = request()->all();

            if (isset($post_data['token'])) {
                [$id, $user_token] = explode('|', $post_data['token'], 2);

                $token_data = DB::table('personal_access_tokens')->where('token', hash('sha256', $user_token))->first();

                if($token_data){
                    $user_id = $token_data->tokenable_id;
                    $user_model = $token_data->tokenable_type;

                    if($user_model == 'App\\Models\\Doctor'){
                        $doctor = Doctor::findOrFail($user_id);
                        $response = [
                            'id' => $doctor->id,
                            'first_name' => $doctor->first_name,
                            'last_name' => $doctor->last_name,
                            'email' => $doctor->email,
                            'code' => $doctor->code,
                            'specialty' => $doctor->specialty,
                            'user_type' => 'professor',
                        ];
                    }

                    else if($user_model == 'App\\Models\\Patient'){
                        $patient = Patient::findOrFail($user_id);

                        $response = [
                            'id' => $patient->id,
                            'first_name' => $patient->first_name,
                            'last_name' => $patient->last_name,
                            'email' => $patient->email,
                            'pathology' => $patient->pathology,
                            'symptoms' => $patient->symptoms,
                            'doctor_id' => $patient->doctor_id,
                            'user_type' => 'patient',
                        ];
                    }

                    return response($response, 201);
                }
                else{
                    return response(null, 500);
                }
            }

            else{
                return response(null, 500);
            }
        }
        catch(Exception $ex){
            info($ex->getMessage().'. File: '.$ex->getFile().' Line: '.$ex->getLine());
            return response($ex->getMessage(), 500);
        }
    }


    public function logout(Request $request) {
        auth()->user()->tokens()->delete();

        $response = [
            'message' => 'Logged out'
        ];

        return response($response, 201);
    }

    //change password for authorized user
    public function change_password(Request $request)
    {
        $input = $request->all();
        $userid = auth()->user()->id;
        $rules = array(

            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
        } else {
            try {
                if ((Hash::check(request('old_password'), auth()->user()->password)) == false) {
                    $arr = array("status" => 400, "message" => "Check your old password.", "data" => array());
                } else if ((Hash::check(request('new_password'), auth()->user()->password)) == true) {
                    $arr = array("status" => 400, "message" => "Please enter a password which is not similar then current password.", "data" => array());
                } else {
                    User::where('id', $userid)->update(['password' => Hash::make($input['new_password'])]);
                    $arr = array("status" => 200, "message" => "Password updated successfully.", "data" => array());
                }
            } catch (\Exception $ex) {
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                } else {
                    $msg = $ex->getMessage();
                }
                $arr = array("status" => 400, "message" => $msg, "data" => array());
            }
        }
        return \Response::json($arr);
    }

    // forgot password creates and sends a reset code via email route 1
    public function ForgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        // Delete all old code that user send before.
        ResetCodePassword::where('email', $request->email)->delete();

        // Generate random code
        $data['code'] = mt_rand(100000, 999999);

        // Create a new code
        $codeData = ResetCodePassword::create($data);

        // Send email to user
        \Mail::to($request->email)->send(new SendCodeResetPassword($codeData->code));

        return response(['message' => trans('passwords.sent')], 200);
    }


    //CodeCheck checks the sent code route 2
    public function CodeCheck(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:reset_code_passwords',
        ]);

        // find the code
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if ($passwordReset->created_at > now()->addHour()) {
            $passwordReset->delete();
            return response(['message' => trans('passwords.code_is_expire')], 422);
        }

        return response([
            'code' => $passwordReset->code,
            'message' => trans('passwords.code_is_valid')
        ], 200);
    }
    // reset resets password route 3
    public function reset(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:reset_code_passwords',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // find the code
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if ($passwordReset->created_at > now()->addHour()) {
            $passwordReset->delete();
            return response(['message' => trans('passwords.code_is_expire')], 422);
        }

        // find user's email
        $user = User::firstWhere('email', $passwordReset->email);

        // update user password

        $user->update(['password' => Hash::make($request['password'])]);

        // delete current code
        $passwordReset->delete();

        return response(['message' =>'password has been successfully reset'], 200);
    }

    // return all exist doctors' codes
    // public function get_codes(){
    //     $codes = Doctor::pluck('code')->all();
    //     return $codes;
    // }

}
