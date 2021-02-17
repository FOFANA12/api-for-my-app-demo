<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\SoftDeletes;
class Locale extends Model
{
    use SoftDeletes;

    public $table = 'locales';
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','code','libelle','statut','created_by','updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
