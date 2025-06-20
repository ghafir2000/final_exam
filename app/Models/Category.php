<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];



    public function categories(){
        return $this->hasMany(Category::class);
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function products(){
        return $this->belongsToMany(Product::class, 'category_product');
    }


}
