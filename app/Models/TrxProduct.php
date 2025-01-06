<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrxProduct extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [];
    protected $guarded = [];


    public function TrxDebts()
    {
        return $this->hasMany(TrxDebt::class);
    }

    public function TrxCompensations()
    {
        return $this->hasMany(TrxCompensation::class);
    }

    public function Mutations()
    {
        return $this->hasMany(Mutation::class);
    }

    public function Product()
    {
        return $this->belongsTo(Product::class);
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function Bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
