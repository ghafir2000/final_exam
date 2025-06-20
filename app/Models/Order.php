<?php

namespace App\Models;

use App\Enums\OrderEnums;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function getStatusLabel(): string
    {
        return OrderEnums::label($this->status);
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class)->withTrashed();
    }

    public function payable()
    {
        return $this->morphOne(Payment::class, 'payable');
    }

}
