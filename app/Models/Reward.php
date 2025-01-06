<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reward extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [];
    protected $guarded = [];

    public function TrxRewards()
    {
        return $this->hasMany(TrxReward::class);
    }
}
