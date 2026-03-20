<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'author_name',
        'flickr_link',
        'thumbnail_url',
        'thumbnail_width',
        'thumbnail_height',
        'is_published',
    ];

    protected $casts = [
        'thumbnail_width' => 'integer',
        'thumbnail_height' => 'integer',
        'is_published' => 'boolean',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
