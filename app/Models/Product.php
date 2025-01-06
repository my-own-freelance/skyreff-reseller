<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [];
    protected $guarded = [];

    public function ProductImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function TrxProducts()
    {
        return $this->hasMany(TrxProduct::class);
    }

    public function ProductCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
