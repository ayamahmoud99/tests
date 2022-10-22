<?php

namespace App\Models;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Doctor extends Authenticatable

{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'code',
        'specialty',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // protected $guarded = [];

    protected $guard = 'doctor';

    // set hashed password
    public function setPasswordAttribute($value){
        $this->attributes['password'] = Hash::make($value);
    }

    // check if the email is doctor's email or not
//    function isDoctor($email) : bool{
//        $doctor = \App\Models\Doctor::whereEmail($email)->exists();
//
//        return $doctor ? true : false;
//    }

    // one to many Relationship
    public function patients(){
        return $this->hasMany(Patient::class);
    }
}
