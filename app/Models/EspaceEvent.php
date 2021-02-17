<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EspaceEvent extends Model
{
    use SoftDeletes;

    public $table = 'espace_events';
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','espace','start_date','end_date','created_by','updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
