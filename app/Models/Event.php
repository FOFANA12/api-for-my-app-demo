<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    public $table = 'events';
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','date','nom','prix','max_invite','description','publier','publication_date','created_by','updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
