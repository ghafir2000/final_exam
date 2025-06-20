<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function userable()
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function remindable()
    {
        return null; //overwtitten because user admin does not have reminders for booking and services, just in case of bad query
    }
    
    
}
