<?php

declare(strict_types = 1);

namespace Elie\Core\Controller;

use Psr\Container\ContainerInterface;

/**
 * This the main controller that all controllers must implement.
 */
interface ControllerInterface
{

    public function __construct(ContainerInterface $container);

    /**
     * If doRun returns false: preRun, run and
     * postRun won't be called.
     */
    public function doRun(): bool;

    /**
     * A preRun to be called before each
     * action call.
     *
     * @return array data to be renedered or [].
     */
    public function preRun(): array;

    /**
     * Run the provided action.
     * Each URL (controller, action) must have one class.
     *
     * @param array $params Not required. Params passed to the url.
     *
     * @return array data to be renedered or [].
     */
    public function run(array $params = []): array;

    /**
     * A postRun to be called after each action call.
     *
     * @return array data to be renedered or [].
     */
    public function postRun(): array;

    /**
     * Redirect transaction.
     *
     * @param string $controller Controller needed.
     * @param string $action Action needed.
     * @param array  $params Optional params needed optional. key/value pairs
     */
    public function redirect($controller, $action, array $params = []): void;
}
