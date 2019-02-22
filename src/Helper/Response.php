<?php

declare(strict_types = 1);

namespace Elie\Core\Helper;

class Response
{

    /**
     * Set HTTP Status Header.
     * @codeCoverageIgnore
     */
    public static function error404(): void
    {
        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? '';

        if (substr(php_sapi_name(), 0, 3) === 'cgi') {
            header('Status: 404 Not Found', true);
        } elseif ($protocol === 'HTTP/1.0') {
            header('HTTP/1.0 404 Not Found', true, 404);
        } else {
            header('HTTP/1.1 404 Not Found', true, 404);
        }
    }
}
