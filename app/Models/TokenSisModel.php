<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenSisModel extends Model
{
    protected $table = 'tb_tokensis';
    public $timestamps = true;
    
    protected $fillable = ['token'];
}
