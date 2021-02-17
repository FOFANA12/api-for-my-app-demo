<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use App\Notifications\CustomerPasswordResetNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
class Customer extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes;

    public $table = 'customers';
    public $incrementing = false;
    public $keyType = 'string';

    protected $guard = 'customer';

    protected $fillable = [
        'id','nom','prenom','entreprise','adresse','member_statut','email','telephone','num_passeport',
        'passeport_file','passeport_file_name', 'password', 'condition_medical', 'contact_urgence_nom','contact_urgence_telephone',
        'image', 'civilite', 'locale', 'statut','created_by','updated_by'
    ];

    protected $hidden = [
        'password'
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
        $this->notify(new CustomerPasswordResetNotification($token));
    }

    public static function generatePassword()
    {
        return Hash::make(str_random(8));
    }

    public function routeNotificationForMail()
    {
        return $this->email;
    }

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

   /* public function receivesBroadcastNotificationsOn()
    {
        return 'customers-'.$this->id;
    }*/

    public function notificationMessages()
    {
        return $this->morphMany(NotificationsMessages::class, 'notificationable', 'notifiable_type','notifiable_id','id');
    }

}
