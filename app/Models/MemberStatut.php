<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class MemberStatut extends Model
{
    use SoftDeletes;

    public $table = 'member_statuts';
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','nom','statut','created_by','updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
