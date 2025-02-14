<?php

namespace App\Models;

use App\Traits\HasChildren;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class Article
 * @package App\Models
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $slug
 * @property int $priority
 * @property string $title
 * @property string $content
 * @property string|null $image
 * @property string|null $image_caption
 * @property bool $is_published
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Article|null $parent
 * @property-read Collection|Tag[] $tags
 *
 */
class Article extends Model
{
    use HasFactory, HasBuilder, HasChildren;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'slug',
        'priority',
        'title',
        'content',
        'image',
        'image_caption',
        'is_published',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'priority' => 'integer',
        'is_published' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'parent_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Article::class, 'parent_id');
    }
}
