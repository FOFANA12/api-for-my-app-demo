<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\SoftDeletes;
class NotificationsMessages extends Model
{
    use SoftDeletes;

    public $table = 'notifications_messages';
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','type','notifiable_type','notifiable_id','data','favoris','read_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }

    public function notificationable()
    {
        return $this->morphTo('notificationable','notifiable_type','notifiable_id','id');
    }
}
