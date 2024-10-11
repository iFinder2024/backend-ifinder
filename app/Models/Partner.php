<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Partner extends Authenticatable implements JWTSubject
{
    protected $table = 'tb_users';
    protected $primaryKey = 'userid';

    protected $fillable = [
        'email', 'password',
    ];

    public function getJWTIdentifier()
    {
        // return $this->getKey();
        return (string) $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
