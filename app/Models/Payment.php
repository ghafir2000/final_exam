<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentEnums;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function getStatusLabel(): string
    {
        return PaymentEnums::label($this->status);
    }


    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function payable()
    {
        return $this->morphTo();
    }
}
