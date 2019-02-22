<?php

declare(strict_types = 1);

namespace Elie\Core\Router;

/**
 * This is a router class that defines controller and action through
 * the url.
 */
interface RouterInterface
{

    /**
     * Creates a router class to manage urls.
     * config Router key should contains:
     *  - controller:string:optional:Default application controller. home is the default value<br/>
     *  - action:string:optional:Default application action. index is the default value<br/>
     *  - protocol:string:optional: Any ProtocolInterface class.<br/>
     *  - routes:array:required: it contains a list of expected routes to be resolved:<br/>
     *  <code>
     *      // the following route has no params
     *      'home'=>array(<br/>
     *          'namespace'=>string:optional (if no namespace is needed):Full namespace Controller\\Home\\<br/>
     *          'controller'=>string:optional: if not set, default application controller is used<br/>
     *          'action'=>string:optional: if not set, default application action is used<br/>
     *          'params'=>array:optional: array of default key/value params<br/>
     *      ),<br/>
     *      // with *, routes accepts params
     *      // all keys after product will be put in params (key/value pair)<br/>
     *      'product/*'=>array(<br/>
     *          'namespace'=>string:optional (if no namespace is needed):Full namespace Controller\\Product\\<br/>
     *          'controller'=>string:optional: if not set, default application controller is used<br/>
     *          'action'=>string:optional: if not set, default application action is used<br/>
     *          'params'=>array:optional: array of key/value params<br/>
     *      )<br/>
     *  </code>
     * @param array $params Default parameter for router class.
     */
    public function __construct(array $params = []);

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
