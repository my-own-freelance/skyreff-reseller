<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrxReward extends Model
{
    use HasFactory;
    protected $fillable = [];
    protected $guarded = [];

    public function User(){
        return $this->belongsTo(User::class);
    }

    public function Reward(){
        return $this->belongsTo(Reward::class);
    }
}
