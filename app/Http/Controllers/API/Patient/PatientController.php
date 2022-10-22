<?php

namespace App\Http\Controllers\API\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use DB;

//models
use App\Models\Doctor;
use App\Models\Patient;

class PatientController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:patient');
    // }

    // public function getDoctor($patient_id){
    //     try{
    //         // $patient = auth()->guard('patient')->user();
    //         // $patient_id = $patient->id;

    //         if($patient_id){
    //             $doctor = Patient::findOrFail($patient_id)->doctor;

    //             if($doctor){
    //                 $response = [
    //                     'id' => $doctor->id,
    //                     'first_name' => $doctor->first_name,
    //                     'last_name' => $doctor->last_name,
    //                     'email' => $doctor->email,
    //                     'code' => $doctor->code,
    //                     'specialty' => $doctor->specialty,
    //                     'user_type' => 'professor',
    //                 ];

    //                 return response($response, 201);
    //             }
    //             else{
    //                 return response(['error' => 'NO Doctor'], 200);
    //             }
    //         }
    //         else{
    //             return response(['error' => 'NO Patient ID'], 200);
    //         }
    //     }
    //     catch(Exception $ex){
    //         info($ex->getMessage().'. File: '.$ex->getFile().' Line: '.$ex->getLine());
    //         return response()->json($ex->getMessage(), 500);
    //     }
    // }

    public function update_profile(){
        try{
            return DB::transaction(function() {
                $rules = [
                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                    'email' => 'required|email|unique:patients,email',
                    // 'professor_code' => 'nullable|string|exist:doctors,code',
                    'pathology' => 'nullable|string',
                    'symptoms' => 'nullable|string',
                    // 'profile_photo'=>'nullable|image|mimes:jpg,bmp,png'
                ];

                $form_data = [
                    'first_name'=>request()->first_name,
                    'last_name'=>request()->last_name,
                    'email'=>request()->email,
                    'pathology'=>request()->pathology,
                    'symptoms'=>request()->symptoms,
                ];

                $patient = Patient::findOrFail(request()->id);
                if($patient->email == request()->email){
                    $rules['email'] = 'required|email';
                }
                else{
                    $rules['email'] = 'required|email|unique:patients,email';
                }

                // if(request()->password) {
                //     $rules['password'] = 'confirmed|string';
                //     $form_data['password'] = request()->password;
                // }

                $validator = Validator::make(request()->all(), $rules);

                if ($validator->fails()) {
                    return response()->json([
                        'message'=>'Validations fails',
                        'errors'=>$validator->errors()
                    ],422);
                }


                // if(request()->hasFile('profile_photo')){
                //     if($user->profile_photo){
                //         $old_path=public_path().'/uploads/profile_images/'.$user->profile_photo;
                //         if(File::exists($old_path)){
                //             File::delete($old_path);
                //         }
                //     }

                //     $image_name='profile-image-'.time().'.'.request()->profile_photo->extension();
                //     request()->profile_photo->move(public_path('/uploads/profile_images'),$image_name);
                // }else{
                //     $image_name=$user->profile_photo;
                // }

                // find the assigned professor
                // $doctor = Doctor::whereCode(request()->professor_code)->first();
                // $doctor_id = $doctor ? $doctor->id : null;
                // $form_data['doctor_id'] = $doctor_id;

                $patient->update($form_data);

                return response()->json([
                    'message'=>'Profile successfully updated',
                    'code'=> 200,
                ],200);
            });
        }
        catch(Exception $ex){
            info($ex->getMessage().'. File: '.$ex->getFile().' Line: '.$ex->getLine());
            return response()->json([
                'message'=> $ex->getMessage(),
                'code'=> 500,
            ],200);
        }
    }
}
