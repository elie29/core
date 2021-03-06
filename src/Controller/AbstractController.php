<?php

declare(strict_types = 1);

namespace Elie\Core\Controller;

use Elie\Core\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Elie\Core\Render\RenderInterface;

/**
 * The controller name must be followed by the action e.g: HomeIndexController
 * The following methods are implemented with defautl value
 *   - doRun return true
 *   - preRun and postRun return []
 */
abstract class AbstractController implements ControllerInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function doRun(): bool
    {
        return true;
    }

    public function preRun(): array
    {
        return [];
    }

    public function postRun(): array
    {
        return [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function redirect($controller, $action, array $params = []): void
    {
        $router = $this->getRouter();
        $url = $router->create($controller, $action, $params);

        header('Location: ' . $url);
        die();
    }

    public function getRender(): RenderInterface
    {
        return $this->container->get(RenderInterface::class);
    }

    public function getRouter(): RouterInterface
    {
        return  $this->container->get(RouterInterface::class);
    }
}
