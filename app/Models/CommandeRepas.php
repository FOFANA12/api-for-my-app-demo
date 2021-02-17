<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class CommandeRepas extends Model
{
    use SoftDeletes;

    public $table = 'commande_repas';
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','reference','customer','date','statut',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
