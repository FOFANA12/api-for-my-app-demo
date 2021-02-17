<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EspaceInactivePeriode extends Model
{
    use SoftDeletes;

    public $table = 'espace_inactive_periodes';
    public $incrementing = false;
    public $timestamps = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','espace_event','text'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
