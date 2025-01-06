<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [];
    protected $guarded = [];

    public function SubDistricts()
    {
        return $this->hasMany(SubDistrict::class);
    }

    public function Users()
    {
        return $this->hasMany(User::class);
    }
}
