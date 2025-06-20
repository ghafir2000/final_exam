<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable = ['chat_id', 'message', 'sender_id', 'sender_type'];

    public function chat()
    {    
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->morphTo();

    }
}
