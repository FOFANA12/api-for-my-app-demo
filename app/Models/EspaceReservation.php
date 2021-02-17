<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EspaceReservation extends Model
{
    use SoftDeletes;

    public $table = 'espace_reservations';
    public $incrementing = false;
    public $timestamps = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','espace_event','customer','statut'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
