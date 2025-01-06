<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrxCommission extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [];
    protected $guarded = [];

    public function Mutations()
    {
        return $this->hasMany(Mutation::class);
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
