<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class EventInvitation extends Model
{
    use SoftDeletes;

    public $table = 'event_invitations';
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','event','customer','participation','approbation','participation_date','approbation_date','notify'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
