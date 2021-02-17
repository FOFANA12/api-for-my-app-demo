<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventImage extends Model
{
    use SoftDeletes;

    public $table = 'event_images';
    public $incrementing = false;
    public $timestamps = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','event','file_id','file_name','description'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
