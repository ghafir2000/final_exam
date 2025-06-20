<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // If your 'embeddings' table uses soft deletes

class Embedding extends Model
{
    use HasFactory, SoftDeletes; // Add SoftDeletes if your table has a deleted_at column

    /**
     * The attributes that are mass assignable.
     *
     * IMPORTANT: 'embedding_vector' and 'text_for_llm_embedding'
     * must be here if you are setting them via the create() method's array.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'text_for_llm_embedding',
        'embedding_vector',
        'embeddable_id',
        'embeddable_type',
        // Add any other fields you directly assign in create([...]) or fill([...])
    ];

    /**
     * The attributes that should be cast.
     *
     * THIS IS CRUCIAL FOR THE "Array to string conversion" ERROR.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'embedding_vector' => 'array', // Use 'array' or 'json'. 'array' is common.
    ];

    /**
     * Get the parent embeddable model (Service or Product).
     */
    public function embeddable()
    {
        return $this->morphTo();
    }
}