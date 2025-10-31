<?php

namespace App\Models;

use App\Notifications\ApiVerifyEmail;
use App\Notifications\ApiResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Send the email verification notification for API.
     */

    public function sendEmailVerificationNotification()
    {
        $this->notify(new ApiVerifyEmail);
    }


    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ApiResetPasswordNotification($token));
    }

   
}