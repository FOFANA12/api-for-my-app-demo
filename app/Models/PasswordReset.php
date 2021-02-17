<?php

namespace App\Models;

use App\Helpers\UUIDHelpers;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    public $table = 'password_resets';
    public $incrementing = false;
    public $timestamps = false;
    public $keyType = 'string';

    protected $fillable = [
        'id','email', 'token', 'expire_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->id = UUIDHelpers::getUUID();
        });
    }
}
