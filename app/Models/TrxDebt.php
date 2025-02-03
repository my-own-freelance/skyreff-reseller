<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrxDebt extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [];
    protected $guarded = [];

    public function TrxProduct(){
        return $this->belongsTo(TrxProduct::class);
    }

    public function Bank(){
        return $this->belongsTo(Bank::class);
    }

    public function User() {
        return $this->belongsTo(User::class);
    }
}
