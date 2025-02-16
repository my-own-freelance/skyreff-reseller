<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrxTopup extends Model
{
    use HasFactory,  SoftDeletes;
    protected $fillable = [];
    protected $guarded = [];

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function Bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function MutationBalances()
    {
        return $this->hasMany(MutationBalance::class);
    }
}
