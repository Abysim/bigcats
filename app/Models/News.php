<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class News extends Model
{
    use HasFactory, HasBuilder;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'slug',
        'title',
        'content',
        'source_name',
        'source_url',
        'image',
        'image_caption',
        'author',
        'is_original',
        'is_published',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'date' => 'date',
        'is_original' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }
}
