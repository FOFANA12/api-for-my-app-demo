<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\UserPasswordResetNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
class User extends Authenticatable implements JWTSubject
{
    use SoftDeletes, Notifiable, HasRoles;
    public $incrementing = false;
    public $keyType = 'string';

    protected $guard_name = 'api';
    protected $guard = 'user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','nom','telephone', 'email', 'password','image',
        'civilite','locale','statut','created_by','updated_by'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserPasswordResetNotification($token));
    }

    public static function generatePassword()
    {
        return Hash::make(str_random(8));
    }

    /*
    public function receivesBroadcastNotificationsOn()
    {
        return 'users-'.$this->id;
    }
*/
    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function notificationMessages()
    {
        return $this->morphMany(NotificationsMessages::class, 'notificationable', 'notifiable_type','notifiable_id','id');
    }

    public static function defaultUser()
    {

        $user = new User();
        $user->nom = 'SuperAdmin';
        $user->statut = true;
        $user->id = UUIDHelpers::getUUID();
        $user->email = 'supadmin@supadmin.com';
        $user->telephone = '123456';
        $user->password = Hash::make('123456');
        return $user;
    }
}
