<?php

namespace App\Traits;

use App\Filament\App\Resources\NewsResource\Widgets\LatestNews;

trait HasLatestNewsFooter
{
    protected function getFooterWidgets(): array
    {
        return [
            LatestNews::make([
                'count' => 6,
            ]),
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 1,
        ];
    }
}
