<?php

namespace App\Models;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $guarded = ['id', 'created_at', 'updated_at'];

    
    protected $fillable = [
        'customer_code',
    ];



    public function userable()
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }


    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

}
