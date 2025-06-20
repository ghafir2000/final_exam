<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pet extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;


    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function records()
    {
        return $this->hasManyThrough(
            Record::class,    // The final model we want to access (Record)
            Booking::class,   // The intermediate model (Booking)
            'pet_id',         // Foreign key on the 'bookings' table (connecting Pet to Booking)
            'booking_id',     // Foreign key on the 'records' table (connecting Booking to Record)
            'id',             // Local key on the 'pets' table (PK of Pet)
            'id'              // Local key on the 'bookings' table (PK of Booking)
        );
    }


    public function breed()
    {
        return $this->belongsTo(Breed::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
