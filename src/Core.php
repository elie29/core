<?php

declare(strict_types = 1);

namespace Elie\Core;

use Elie\Core\Controller\ControllerInterface;
use Elie\Core\Render\RenderInterface;
use Elie\Core\Router\RouterInterface;
use Psr\Container\ContainerInterface;

/**
 * This class uses Psr\Container to manage Dependency Injection.
 * The application depends on:
 *    - RouterInterface
 *    - RenderInterface
 * Controller class should end with Controller eg.
 *  HomeIndexController
 *  ProductSaveController
 * It instantiates dynamically Controllers that implements Controller Interface.
 */
class Core
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Run the application by calling the route system,
     * the controller and the action.
     *
     * This function will merge preRun, run and postRun datas.
     *
     * @throws CoreException
     */
    public function run(): void
    {
        $router = $this->getRouter();

        // Retrieve the class controller
        $class = $router->getNamespace()
               . ucfirst($router->getController())
               . ucfirst($router->getAction())
               . 'Controller';

        if (! class_exists($class, true)) {
            throw new CoreException("Class not found {$class}");
        }

        /* @var $controller ControllerInterface */
        $controller = new $class($this->container);

        $data = [];
        if ($controller->doRun()) {
            $data = $this->getData($controller, $router);
        }

        // Render the layout
        echo $this->getRender()->fetchLayout($data);
    }

    /**
     * Retrieves the router class.
     */
    protected function getRouter(): RouterInterface
    {
        return $this->container->get(RouterInterface::class);
    }

    /**
     * Retrieves the render class.
     */
    protected function getRender(): RenderInterface
    {
        return $this->container->get(RenderInterface::class);
    }

    /**
     * Executes the preRun, run and postRun.
     * It returns the merged data of the three consecutive functions.
     */
    protected function getData(ControllerInterface $controller, RouterInterface $router): array
    {
        return array_merge(
            $controller->preRun(),
            $controller->run($router->getParams()),
            $controller->postRun()
        );
    }
}
