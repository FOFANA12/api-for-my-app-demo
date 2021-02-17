<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ItemCommandeRepas extends Model
{
    use SoftDeletes;

    public $table = 'item_commande_repas';
    public $incrementing = false;
    public $timestamps = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','commande','menu','quantite','prix',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
