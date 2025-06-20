<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'available_times' => 'json',
        'price' => 'float'
    ];




    public function breeds()
    {
        return $this->belongsToMany(Breed::class,'breed_service','service_id','breed_id');
    }

    public function wishable()
    {
        return $this->morphMany(Wish::class, 'wishable');
    }

    public function servicable()
    {
        return $this->morphTo();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function ebeddable()
    {
        return $this->morphOne(Embedding::class, 'embeddable')->withDefault();
    }

}
