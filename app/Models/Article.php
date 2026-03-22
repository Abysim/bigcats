<?php

namespace App\Models;

use App\Traits\HasChildren;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

/**
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
 * @property bool $is_featured
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Article|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<Article> $children
 * @property-read \Illuminate\Database\Eloquent\Collection<Tag> $tags
 */
class Article extends Model implements Sitemapable
{
    use HasFactory, HasBuilder, HasChildren, HasSEO;

    public const NAV_CACHE_KEY = 'nav_articles';

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
        'is_featured',
    ];

    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'priority' => 'integer',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'parent_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Article::class, 'parent_id');
    }

    public function publishedChildren(): HasMany
    {
        return $this->children()->published()->orderBy('priority');
    }

    public function featuredChildren(): HasMany
    {
        return $this->children()->published()->featured()->orderBy('priority');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFrontpage($query)
    {
        return $query->whereNull('parent_id');
    }

    public function isFrontpage(): bool
    {
        return $this->parent_id === null;
    }

    public function getUrl(): string
    {
        $slugs = collect();
        $article = $this;
        while ($article && $article->parent_id !== null) {
            $slugs->prepend($article->slug);
            $article = $article->parent;
        }
        return '/' . $slugs->implode('/');
    }

    public function getAncestors(): \Illuminate\Support\Collection
    {
        $ancestors = collect();
        $article = $this->parent;
        while ($article) {
            $ancestors->prepend($article);
            $article = $article->parent;
        }
        return $ancestors;
    }

    public function getDynamicSEOData(): SEOData
    {
        $title = $this->isFrontpage() && ($homepageTitle = config('seo.title.homepage_title'))
            ? $homepageTitle
            : $this->title;

        return new SEOData(
            title: $title . ' | ' . config('app.name'),
            description: html_entity_decode(Str::of($this->resume ?? $this->content)->stripTags()->limit(160)),
            image: $this->image ? Storage::url($this->image) : null,
            url: $this->isFrontpage() ? url('/') : url($this->getUrl()),
            type: 'article',
            openGraphTitle: $title,
        );
    }

    public function toSitemapTag(): Url|string|array
    {
        return Url::create($this->getUrl())
            ->setLastModificationDate($this->updated_at);
    }
}
