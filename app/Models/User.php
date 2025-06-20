<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Prompts\Prompt;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes, InteractsWithMedia, HasRoles;

    protected $table = 'users';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function userable()
    {
        return $this->morphTo();
    }
        
    public function remindable()
    {
        return $this->morphMany(Reminder::class, 'remindable')
                ->where('remindable_type', '!=', Admin::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function servicable()
    {
        return $this->morphMany(Service::class, 'servicable')
            ->where('servicable_type', '<>', Customer::class)
            ->where('servicable_type', '<>', Admin::class);
    }
    public function productable()
    {
        return $this->morphMany(Product::class, 'productable')
            ->where('productable_type', '<>', Customer::class)
            ->where('productable_type', '<>', Admin::class);
    }
    
    public function chatable()
    {
        return $this->morphMany(Chat::class, 'chatable');
    }
    
    public function rateable()
    {
        return $this->morphMany(Rating::class, 'rateable')
            ->where('rateable_type', '<>', Customer::class)
            ->where('rateable_type', '<>', Admin::class);
    }
    
    public function reportable()
    {
        return $this->morphMany(Report::class, 'reportable');
    }
    
    public function sender()
    {
        return $this->morphMany(Message::class, 'sender');
    }
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
    public function wishes()
    {
        return $this->hasMany(Wish::class);
    }
}

