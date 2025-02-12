<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    use HasFactory, HasBuilder;

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
    ];

    public function children(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TagType::class);
    }

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class);
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }
}
