<?php

namespace App\Modules\User\Models;

use App\Modules\User\Notifications\ResetPasswordNotification;
use App\Modules\User\Notifications\VerifyNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;


/**
 * @method static where(string $string, mixed $login)
 * @method static find(string $string, mixed $id)
 */
class UserModel extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    public $table = 'users';

    protected $primaryKey = 'id';

    protected $fillable = ['id','login', 'email', 'password', 'salt'];

    public $timestamps = false;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getEmailForVerification()
    {
        $this->email;
    }
    public function getEmailForPasswordReset()
    {
        $this->email;
    }
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyNotification);
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification);
    }

}
