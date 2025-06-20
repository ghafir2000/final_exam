<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryProduct extends Pivot
{
    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function categories(){
        return $this->belongsTo(Category::class); 
    }
}
