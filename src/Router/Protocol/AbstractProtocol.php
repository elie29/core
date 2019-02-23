<?php

declare(strict_types = 1);

namespace Elie\Core\Router\Protocol;

use Elie\Core\Router\RouterConst;

/**
 * This class defines all common methods.
 */
abstract class AbstractProtocol implements ProtocolInterface
{

    /**
     * Current definition found from url.
     * @var array
     */
    protected $definition = [];

    /**
     * Different routes stucture.
     * @var array
     */
    protected $routes = [];

    public function __construct(array $routes = [])
    {
        $this->routes = $routes;

        $this->exploreUrl();
    }

    public function getDefinition(): array
    {
        return $this->definition;
    }

    /**
     * Explore the url in path or query protocol.
     */
    abstract protected function exploreUrl(): void;

    /**
     * Set the pathinfo string and map the uri
     * for controller, action and params.
     *
     * @param string $path The url path.
     */
    protected function setRoute(string $path): void
    {
        $this->definition = [];

        $path = $this->clear($path);

        // We explode our given routes: this first definition found will prime.
        foreach ($this->routes as $route => $definition) {
            // Test if route is supposed to accept params
            $hasStar = false;
            if ($this->match($route, $path, $hasStar)) {
                if ($hasStar) {
                    // params are authorised
                    $remain = substr($path, strlen($route) - 1);
                    $params = $remain ? $this->extractParams(explode('/', $remain)) : [];

                    if (isset($definition[RouterConst::PARAMS])) {
                        $params = array_merge($definition[RouterConst::PARAMS], $params);
                    }
                    $definition[RouterConst::PARAMS] = $params;
                }

                $this->definition = $definition;
                return;
            }
        }

        // No route was found or matched, let's try controller/action/[params:id/value]
        $this->explore($path);
    }

    /**
     * Correct and clean the pattern.
     * Trim, striptag and lowercase the url.
     *
     * @param string $pattern The pattern to be cleaned.
     *
     * @return string
     */
    protected function clear(string $pattern): string
    {
        // striptags and trim the pattern
        $pattern = trim(strip_tags($pattern));

        if ('' === $pattern) {
            return '';
        }

        // A-Z, 0-9, - / _ . are ONLY authorized in URL
        $pattern = preg_replace('#[^a-z0-9\-_/\.]+#i', '', $pattern);
        // We remove / from both sides
        $pattern = trim($pattern, '/');
        // Replace multiple slashes in a url, such as /my//dir/url
        $pattern = preg_replace('#\/+#', '/', $pattern);

        // Lowercase the pattern
        return strtolower($pattern);
    }

    /**
     * Matches a route to a url.
     *
     * @param string $route   A given route to be matched.
     * @param string $url     A provided url.
     * @param bool   $hasStar True if the route conatains a star.
     */
    protected function match($route, $url, &$hasStar): bool
    {
        $hasStar = false;

        if (substr($route, -2) === '/*') {
            // We remove the /* from the route
            $route = substr($route, 0, - 2);
            // If route is at the beginning of the url
            if (strpos($url, $route) === 0) {
                $remain = substr($url, strlen($route), 1);
                // be sure that $url = $route/ and not $routeX
                if ($remain === '/' || $remain === '') {
                    $hasStar = true;
                }
                return $hasStar;
            }
        }

        return $url === $route;
    }

    /**
     * All keys are considered as params except
     * controller and action.
     *
     * @param array $urlParts The url remained parts.
     */
    protected function extractParams(array $urlParts): array
    {
        $partsRemaining = count($urlParts);

        // In case we have an odd params
        if ($partsRemaining % 2) {
            throw new ProtocolException('URL parts are not set correctly');
        }

        $params = [];
        // Everything else is considered as key/value pairs
        for ($i = 0; $i < $partsRemaining; $i += 2) {
            $params[$urlParts[$i]] = $urlParts[$i + 1];
        }

        return $params;
    }

    /**
     * try controller/action/[params:id/value]
     */
    protected function explore(string $path): void
    {
        $urlParts = explode('/', $path);

        $controller = array_shift($urlParts);
        if ($controller) {
            $this->definition['controller'] = $controller;
        }

        $action = array_shift($urlParts);
        if ($action) {
            $this->definition['action'] = $action;
        }

        // All other parts are params
        if ($urlParts) {
            $this->definition['params'] = $this->extractParams($urlParts);
        }
    }
}
