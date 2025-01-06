<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrxDebt extends Model
{
    use HasFactory;
    protected $fillable = [];
    protected $guarded = [];

    public function TrxProduct(){
        return $this->belongsTo(TrxProduct::class);
    }

    public function Bank(){
        return $this->belongsTo(Bank::class);
    }
}
