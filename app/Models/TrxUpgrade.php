<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrxUpgrade extends Model
{
    use HasFactory;
    protected $fillable = [];
    protected $guarded = [];

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
