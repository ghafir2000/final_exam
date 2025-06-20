<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartProduct extends Pivot
{
    use SoftDeletes;

    public function cart()
    {
        return $this->belongsTo(Cart::class)->withTrashed();
    }
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
