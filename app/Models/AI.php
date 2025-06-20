<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AI extends Model
{
    use HasFactory;
    use InteractsWithMedia;
    
    protected $table = 'ais';

    protected $fillable = [
        'name',
        'model',
    ];
 


    public function chatable()
    {
        return $this->morphOne(Chat::class, 'chatable');
    }

}
