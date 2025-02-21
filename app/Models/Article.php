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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\ImageMeta;
use RalphJSmit\Laravel\SEO\Support\SEOData;

/**
 * Class Article
 * @package App\Models
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $slug
 * @property int $priority
 * @property string $title
 * @property string $resume
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
    use HasFactory, HasBuilder, HasChildren, HasSEO;

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
        'resume',
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

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->title . ' | ' . config('app.name'),
            description: html_entity_decode(Str::of($this->resume ?? $this->content)->stripTags()->limit(160)),
            image: asset(Storage::url($this->image)),
            imageMeta: new ImageMeta(public_path($this->image)),
            type: 'article',
            openGraphTitle: $this->title,
        );
    }
}
