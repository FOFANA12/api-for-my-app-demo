<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerNote extends Model
{
    use SoftDeletes;

    public $table = 'customer_notes';
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','customer','date','sujet','commentaire','created_by','updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
