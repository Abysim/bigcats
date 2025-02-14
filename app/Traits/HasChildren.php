<?php

namespace App\Traits;

use Illuminate\Support\Collection;

/**
 * Trait HasChildren
 * @package App\Traits
 * @property Collection $children
 * @property int $id
 * @method load(string $relation)
 */
trait HasChildren
{
    public function getAllChildrenIds(): Collection
    {
        $allChildrenIds = collect([$this->id]);

        $this->load('children');

        if ($this->children) {
            foreach ($this->children as $child) {
                $allChildrenIds = $allChildrenIds->merge($child->getAllChildrenIds());
            }
        }

        return $allChildrenIds->unique();
    }
}
