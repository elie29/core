<?php

declare(strict_types = 1);

namespace Elie\Core\Router\Protocol\Path;

use Elie\Core\Router\RouterConst;
use Elie\Core\Router\Protocol\AbstractProtocol;

/**
 * A protocol router that manages path info.
 */
class Path extends AbstractProtocol
{

    protected function exploreUrl(): void
    {
        // PATH_INFO exists?
        $path = $_SERVER[RouterConst::PATH_INFO] ?? '';

        if (! $path && ! empty($_SERVER[RouterConst::REQUEST_URI])) {
            $path = $this->getUriFromRequest();
        } elseif (strpos($path, $_SERVER[RouterConst::SCRIPT_NAME]) === 0) {
            $path = substr($path, strlen($_SERVER[RouterConst::SCRIPT_NAME]));
        }

        // Remove htm extension
        $path = str_ireplace('.htm', '', $path);

        $this->setRoute($path);
    }

    /**
     * Get the uri from request if it is not set in PATH_INFO.
     *
     * @return string
     */
    protected function getUriFromRequest(): string
    {
        $uri = $_SERVER[RouterConst::REQUEST_URI];

        // We remove script name from request uri
        if (strpos($uri, $_SERVER[RouterConst::SCRIPT_NAME]) === 0) {
            // We leave the pathinfo
            $uri = substr($uri, strlen($_SERVER[RouterConst::SCRIPT_NAME]));
        } elseif (strpos($uri, dirname($_SERVER[RouterConst::SCRIPT_NAME])) === 0) {
            $uri = substr($uri, strlen(dirname($_SERVER[RouterConst::SCRIPT_NAME])));
        }

        // remove query string
        return preg_replace('/\?(.)*/', '', $uri);
    }
}
