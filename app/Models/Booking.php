<?php

namespace App\Models;

use App\Enums\BookingEnums;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function getStatusLabel(): string
    {
        return BookingEnums::label($this->status);
    }


    public function record()
    {
        return $this->hasOne(Record::class);
    }

    public function payable()
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
