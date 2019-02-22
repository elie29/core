<?php

declare(strict_types = 1);

namespace Elie\Core\Router\Protocol;

interface ProtocolInterface
{

    /**
     * @param array $routes Different routes structure. (cf. RouterInterface)
     */
    public function __construct(array $routes = []);

    /**
     * Returns the router current definition:
     * controller, action, namespace AND/OR params.
     */
    public function getDefinition(): array;
}
