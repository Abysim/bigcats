<?php

namespace App\Sanitizers;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

class VideoEmbedSrcSanitizer implements AttributeSanitizerInterface
{
    private const ALLOWED_SCHEMES = ['http', 'https'];

    private const ALLOWED_HOSTS = [
        'youtube.com',
        'www.youtube.com',
        'm.youtube.com',
        'youtube-nocookie.com',
        'www.youtube-nocookie.com',
        'youtu.be',
        'vimeo.com',
        'player.vimeo.com',
        'dailymotion.com',
        'www.dailymotion.com',
        'geo.dailymotion.com',
    ];

    public function getSupportedElements(): ?array
    {
        return ['iframe'];
    }

    public function getSupportedAttributes(): ?array
    {
        return ['src'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        $url = parse_url($value);
        if (! is_array($url) || ! isset($url['scheme'], $url['host'])) {
            return null;
        }

        if (! in_array(strtolower($url['scheme']), self::ALLOWED_SCHEMES, true)) {
            return null;
        }

        if (! in_array(strtolower($url['host']), self::ALLOWED_HOSTS, true)) {
            return null;
        }

        return $value;
    }
}
