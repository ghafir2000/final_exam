<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    

    protected $guarded = ['id', 'created_at', 'updated_at'];

    
    protected $casts = [
        'price' => 'float'
    ];


    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_product', 'product_id','cart_id')->withPivot('quantity','id');
    
    }


    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    public function wishable()
    {
        return $this->morphMany(Wish::class, 'wishable');
    }

    public function productable()
    {
        return $this->morphTo();
    }

    public function ebeddable()
    {
        return $this->morphOne(Embedding::class, 'embeddable')->withDefault();
    }
}
