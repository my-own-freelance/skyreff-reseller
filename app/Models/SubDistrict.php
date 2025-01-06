<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubDistrict extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [];
    protected $guarded = [];

    public function District()
    {
        return $this->belongsTo(District::class);
    }

    public function Users()
    {
        return $this->hasMany(User::class);
    }
}
