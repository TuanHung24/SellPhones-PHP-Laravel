<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory,SoftDeletes;
    protected $table='products';
    public function product_detail(){
        return $this->hasMany(ProductDetail::class);
    }
    public function brand(){
        return $this->belongsTo(Brand::class);
    }
    public function product_description()
    {
        return $this->hasMany(ProductDescription::class, 'product_id');
    }

    public function img_product()
    {
        return $this->hasMany(ImgProduct::class, 'product_id');
    }
}
