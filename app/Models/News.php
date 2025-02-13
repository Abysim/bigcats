<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property Carbon $date
 * @property string $slug
 * @property string $title
 * @property string $content
 * @property string $source_name
 * @property string $source_url
 * @property string $image
 * @property string $image_caption
 * @property string $author
 * @property bool $is_original
 * @property bool $is_published
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection|Tag[] $tags
 */
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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
