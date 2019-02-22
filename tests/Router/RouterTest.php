<?php

declare(strict_types = 1);

namespace Elie\Core\Router;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{

    public function testDefaultRouterParams(): void
    {
        $router = new Router([
            RouterConst::ROUTES => [
                // at least one route is required in ROUTES array
                '' => []
            ]
        ]);

        assertThat($router->getNamespace(), is(emptyString()));
        assertThat($router->getController(), is('home'));
        assertThat($router->getAction(), is('index'));
        assertThat($router->getParams(), is(emptyArray()));
        assertThat($router->getCurrentUrl(), is('?route=home/index'));
        assertThat($router->getImplodedParams(), is(''));

        $url = $router->create('product', 'care', ['item' => 'mask', 'view' => 'detail']);
        assertThat($url, is('?route=product/care/item/mask/view/detail'));
    }

    public function testDefaultRouteProtocol(): void
    {
        $router = new Router([
            RouterConst::CONTROLLER => 'product',
            RouterConst::ACTION => 'save',
            RouterConst::ROUTES => [
                '' => [
                    RouterConst::NAMESPACE => 'App\\Controller\\',
                    RouterConst::PARAMS => ['item' => 'care']
                ]
            ]
        ]);

        assertThat($router->getNamespace(), is('App\\Controller\\'));
        assertThat($router->getController(), is('product'));
        assertThat($router->getAction(), is('save'));
        assertThat($router->getParams(), is(['item' => 'care']));

        $url = $router->create('product', 'care', ['item' => 'mask', 'view' => 'detail']);
        assertThat($url, is('?route=product/care/item/mask/view/detail'));

        assertThat($router->getCurrentUrl(), is('?route=product/save/item/care'));
        assertThat($router->getImplodedParams(), is('/item/care'));
    }

    public function testRouterWithDifferentParams(): void
    {
        $router = new Router([
            RouterConst::PROTOCOL => RouterConst::PATH_CLASSNAME,
            RouterConst::ROUTES => [
                '' => []
            ]
        ]);

        assertThat($router->getNamespace(), is(emptyString()));
        assertThat($router->getController(), is('home'));
        assertThat($router->getAction(), is('index'));
        assertThat($router->getParams(), is(emptyArray()));

        $url = $router->create('product', 'care', ['item' => 'mask', 'view' => 'detail']);
        assertThat($url, is('product/care/item/mask/view/detail.htm'));

        assertThat($router->getCurrentUrl(), is('home/index.htm'));
        assertThat($router->getImplodedParams(), is(''));
    }
}
