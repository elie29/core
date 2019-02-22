<?php

declare(strict_types = 1);

namespace Elie\Core\Router\Protocol\Query;

use Elie\Core\Router\RouterConst;
use Elie\Core\Router\Protocol\AbstractProtocol;

/**
 * A protocol router that manages query string.
 */
class Query extends AbstractProtocol
{

    protected function exploreUrl(): void
    {
        /*
         * In query string, we juste need to have a
         * route key that contains route's value
         */
        $url = $_GET[RouterConst::ROUTE] ?? '';

        $this->setRoute($url);
    }
}
