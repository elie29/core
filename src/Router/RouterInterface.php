<?php

declare(strict_types = 1);

namespace Elie\Core\Router;

use Psr\Container\ContainerInterface;

/**
 * This is a router class that defines controller and action through
 * the url.
 */
interface RouterInterface
{

    /**
     * Creates a router class to manage urls.
     * config Router key should contains under core/router key:
     * <code>
     *  'core' => [
     *    'router' => [
     *      'namespace'=>string:optional:Default application namespace. empty is the default value
     *      'controller'=>string:optional:Default application controller. home is the default value
     *      'action'=>string:optional:Default application action. index is the default value
     *      'protocol'=>string:optional:Any ProtocolInterface class.
     *      'routes'=>array:required:contains a list of expected routes to be resolved
     *      [
     *        // the following route has no params
     *        'home'=>[
     *          'namespace'=>string:optional (if no specific namespace is needed):Full namespace Controller\\Home\\
     *          'controller'=>string:optional: if not set, default application controller is used
     *          'action'=>string:optional: if not set, default application action is used
     *          'params'=>array:optional: array of default key/value params
     *        ],
     *        // with *, routes accepts params
     *        // all keys after product will be put in params (key/value pair)
     *        'product/*'=>[
     *          'namespace'=>string:optional:Full namespace Controller\\Product\\
     *          'controller'=>string:optional: if not set, default application controller is used
     *          'action'=>string:optional: if not set, default application action is used
     *          'params'=>array:optional: array of key/value params
     *        ]
     *      ]
     *    ]
     * ]
     */
    public function __construct(ContainerInterface $container);

    /**
     * Retrieve the controller in url or the default controller.
     */
    public function getController(): string;

    /**
     * Retrieve the action in url or the default action.
     */
    public function getAction(): string;

    /**
     * Retrieve the params in url.
     */
    public function getParams(): array;

    /**
     * Return the current namespace.
     */
    public function getNamespace(): string;

    /**
     * Create a new url depending on protocol.
     *
     * @param string $controller Controller needed.
     * @param string $action     Action needed.
     * @param array  $params     Params needed, pairs key/value
     *
     * if protocol is QUERY STRING:
     *     ?route=controller/index/[params]
     * else
     *     controller/index/[params].htm
     */
    public function create($controller, $action, array $params = []): string;

    /**
     * Get the current URL path.
     *
     * if protocol is QUERY STRING:
     *     ?route=controller/index/[params]
     * else
     *     controller/index/[params].htm
     */
    public function getCurrentUrl(): string;

    /**
     * Gets the params as a string key/value
     */
    public function getImplodedParams(): string;
}
