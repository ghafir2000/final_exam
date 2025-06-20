<?php

namespace App\Models;

use App\Enums\ChatEnums;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function getStatusLabel(): string
    {
        return ChatEnums::label($this->status);
    }

    public function chatable()
    {
        return $this->morphTo();
    }   

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // In App\Models\Chat.php
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest('created_at');
    }

}
