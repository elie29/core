<?php

declare(strict_types = 1);

namespace Elie\Core\Router;

use Elie\Core\Router\Protocol\ProtocolInterface;
use Elie\Core\Router\RouterInterface;

/**
 * This class retrieves controller and action from the url.
 */
class Router implements RouterInterface
{

    /**
     * Query protocol by default.
     * @var string
     */
    protected $protocol = RouterConst::QUERY_CLASSNAME;

    /**
     * Namespace used to instanciate the controller.
     * @var string
     */
    protected $namespace;

    /**
     * Default controller, home if router has found nothing
     * @var string
     */
    protected $defaultController = 'home';

    /**
     * Default action, index if router has found nothing.
     * @var string
     */
    protected $defaultAction = 'index';

    /**
     * @var ProtocolInterface
     */
    protected $router;

    public function __construct(array $params = [])
    {
        if (isset($params[RouterConst::CONTROLLER])) {
            $this->defaultController = $params[RouterConst::CONTROLLER];
        }

        if (isset($params[RouterConst::ACTION])) {
            $this->defaultAction = $params[RouterConst::ACTION];
        }

        if (isset($params[RouterConst::PROTOCOL])) {
            $this->protocol = $params[RouterConst::PROTOCOL];
        }

        $routes = $params[RouterConst::ROUTES] ?? [];

        // Sets the router
        $this->router = new $this->protocol($routes);
    }

    public function getNamespace(): string
    {
        $def = $this->router->getDefinition();

        return $def[RouterConst::NAMESPACE] ?? '';
    }

    public function getController(): string
    {
        $def = $this->router->getDefinition();

        return $def[RouterConst::CONTROLLER] ?? $this->defaultController;
    }

    public function getAction(): string
    {
        $def = $this->router->getDefinition();

        return $def[RouterConst::ACTION] ?? $this->defaultAction;
    }

    public function getParams(): array
    {
        $def = $this->router->getDefinition();

        return $def[RouterConst::PARAMS] ?? [];
    }

    public function create($controller, $action, array $params = []): string
    {
        // controller/action/[params]
        $url = sprintf('%s/%s%s', $controller, $action, $this->implode($params));

        if ($this->protocol === RouterConst::QUERY_CLASSNAME) {
            return sprintf('?%s=%s', RouterConst::ROUTE, $url);
        }

        return $url . '.htm';
    }

    public function getCurrentUrl(): string
    {
        return $this->create(
            $this->getController(),
            $this->getAction(),
            $this->getParams()
        );
    }

    public function getImplodedParams(): string
    {
        return $this->implode($this->getParams());
    }

    /**
     * Implodes params key/value.
     *
     * @param array $params Params to be imploded.
     */
    protected function implode(array $params): string
    {
        $res = [];

        foreach ($params as $k => $v) {
            $res[] = $k . '/' . $v;
        }

        return $res ? '/' . implode('/', $res) : '';
    }
}
