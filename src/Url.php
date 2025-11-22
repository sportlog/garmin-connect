<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

class Url
{
    /**
     * Build URL.
     * 
     * @param string|string[] $url The URL
     * @param (string|int)[] $queryParams Params to be appended to the url.
     */
    static function build(string|array $url, array $queryParams): string
    {
        if (is_array($url)) {
            $url = implode('/', array_map(fn($segment) => trim($segment, '/'), $url));
        }

        $nonEmptyParams = array_filter($queryParams, fn($value) => $value !== null && $value !== '');
        if (!empty($nonEmptyParams)) {
            $url .= '?' . http_build_query($nonEmptyParams);
        }

        return $url;
    }
}
