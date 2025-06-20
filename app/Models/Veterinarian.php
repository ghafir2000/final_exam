<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Veterinarian extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable = [
        'degree',
        'degree_year',
        'university',
    ];

    
    public function userable()
    {
        return $this->morphOne(User::class, 'userable');
    }
}
