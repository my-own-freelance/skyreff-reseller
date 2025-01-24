<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [];
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function TrxProducts()
    {
        return $this->hasMany(TrxProduct::class);
    }

    public function TrxCommissions()
    {
        return $this->hasMany(TrxCommission::class);
    }

    public function TrxCompensations()
    {
        return $this->hasMany(TrxCompensation::class);
    }

    public function TrxUpgrades()
    {
        return $this->hasMany(TrxUpgrade::class);
    }

    public function TrxRewards()
    {
        return $this->hasMany(TrxReward::class);
    }

    public function Province()
    {
        return $this->belongsTo(Province::class);
    }

    public function District()
    {
        return $this->belongsTo(District::class);
    }

    public function SubDistrict()
    {
        return $this->belongsTo(SubDistrict::class);
    }
}
