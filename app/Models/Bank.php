<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [];
    protected $guarded = [];

    public function TrxProducts()
    {
        return $this->hasMany(TrxProduct::class);
    }

    public function TrxDebts()
    {
        return $this->hasMany(TrxDebt::class);
    }

    public function TrxTopups()
    {
        return $this->hasMany(TrxTopup::class);
    }

}
