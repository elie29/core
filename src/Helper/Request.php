<?php

declare(strict_types = 1);

namespace Elie\Core\Helper;

class Request
{

    public const HTTP_X_REQUESTED_WITH = 'HTTP_X_REQUESTED_WITH';
    public const HTTP_REFERER = 'HTTP_REFERER';

    /**
     * Test if application is set in ajax.
     */
    public static function isAjax(): bool
    {
        return ! empty($_SERVER[self::HTTP_X_REQUESTED_WITH]);
    }

    /**
     * Test if application has a referer.
     *
     * @param string $base The expected application base.
     */
    public static function hasReferer(string $base): bool
    {
        $referer = null;

        if (! empty($_SERVER[self::HTTP_REFERER])) {
            $referer = parse_url($_SERVER[self::HTTP_REFERER], PHP_URL_HOST);
            $base = parse_url($base, PHP_URL_HOST);
        }

        return $base === $referer;
    }
}
