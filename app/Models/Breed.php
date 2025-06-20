<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Breed extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];


    public function services()
    {
        return $this->belongsToMany(Service::class,'breed_service','breed_id','service_id');
    }

    public function pets()    
    {
        return $this->hasMany(Pet::class);
    }
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
