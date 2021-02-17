<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class JWT extends Model
{
    use SoftDeletes;

    public $table = 'j_w_t_s';
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
