<?php

namespace App\Helpers;

use Illuminate\Support\HtmlString;

class SourceLink
{
    public static function format(string $name, ?string $url): HtmlString
    {
        return new HtmlString(
            $url
                ? '<a rel="nofollow" title="' . e($url) . '" href="' . e($url) . '" target="_blank">' . e($name) . '</a>'
                : e($name)
        );
    }
}
