<?php

namespace App\Filament\App\Resources\TagResource\Widgets;

use App\Filament\App\Resources\TagResource;
use App\Models\Tag;
use Filament\Widgets\Widget;

class TagCloud extends Widget
{
    public string $heading = 'Теґи';

    protected static string $view = 'filament.app.resources.tag-resource.widgets.tag-cloud';

    protected int | string | array $columnSpan = 'full';

    public string $relation = 'news';

    public $minSize = 10;

    public $maxSize = 40;

    public function getTags()
    {
        $tags = Tag::query()
            ->whereHas($this->relation)
            ->withCount($this->relation)
            ->orderBy('type_id')
            ->orderBy('name')
            ->get();

        $maxCount = $tags->max($this->relation . '_count');
        $minCount = $tags->min($this->relation . '_count');

        $result = [];
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $weight = $this->calculateWeight($tag->{$this->relation . '_count'}, $minCount, $maxCount);
            $result[] = [
                'weight' => $weight,
                'url' => TagResource::getUrl('view', ['slug' => $tag->slug]),
                'name' => $tag->name,
            ];
        }

        return $result;
    }

    private function calculateWeight($count, $minCount, $maxCount): int
    {
        $minSize = $this->minSize;
        $maxSize = $this->maxSize;

        if ($maxCount === $minCount) return $minSize;

        return (int) round(($count - $minCount) * ($maxSize - $minSize) / ($maxCount - $minCount) + $minSize);
    }

    protected function getHeading(): string
    {
        return $this->heading;
    }
}
