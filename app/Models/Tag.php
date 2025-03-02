<?php

namespace App\Models;

use App\Filament\App\Resources\TagResource;
use App\Traits\HasChildren;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class Tag extends Model implements Sitemapable
{
    use HasFactory, HasBuilder, HasChildren;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'name',
        'parent_id',
        'type_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'type_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(Tag::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'parent_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TagType::class, 'type_id');
    }

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class);
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }

    public function toSitemapTag(): Url | string | array
    {
        $lastNewsDate = $this->news()->where(['is_published' => true])->latest('created_at')->first()?->created_at ?? now();

        return Url::create(TagResource::getUrl('view', ['slug' => $this->slug], panel: 'app'))
            ->setLastModificationDate($lastNewsDate);
    }
}
