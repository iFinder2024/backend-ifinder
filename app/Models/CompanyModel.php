<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyModel extends Model
{
    protected $table = 'tb_users';
    protected $primaryKey = 'companyid';

    public function parent()
    {
        return $this->belongsTo(CompanyModel::class, 'parentid');
    }

    public function subsidiaries()
    {
        return $this->hasMany(CompanyModel::class, 'parentid');
    }
}
