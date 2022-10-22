<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;


use App\Models\Doctor;

class Patient extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'pathology',
        'symptoms',
        'doctor_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard = 'patient';


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($value){
        $this->attributes['password'] = Hash::make($value);
    }

    public function sendPasswordResetNotification($token)
    {
        // $url = 'https://spa.test/reset-password?token=' . $token;
        // $this->notify(new ResetPasswordNotification($url));
        $this->notify(new ResetPasswordNotification($token));
    }

    // check if the email is patient's email or not
    function isPatient($email) : bool{
        $patient = Patient::whereEmail($email)->exists();

        return $patient ? true : false;
    }

    // one to many Relationship
    public function doctor(){
        return $this->belongsTo(Doctor::class);
    }
}
