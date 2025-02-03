<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    use HasFactory;
    protected $fillable = [];
    protected $guarded = [];

    public function TrxProduct() {
        return $this->belongsTo(TrxProduct::class);
    }

    public function TrxCommission() {
        return $this->belongsTo(TrxCommission::class);
    }

    public function User() {
        return $this->belongsTo(User::class);
    }
}
