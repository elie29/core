<?php

declare(strict_types = 1);

namespace Elie\Core\Router;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class RouterTest extends TestCase
{

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    protected function getRouter(array $config = []): RouterInterface
    {
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('config')->andReturn([
            'core' => [
                'router' => $config
            ]
        ]);

        return new Router($container);
    }

    public function testDefaultRouterParams(): void
    {
        $router = $this->getRouter();

        assertThat($router->getNamespace(), is(emptyString()));
        assertThat($router->getController(), is('home'));
        assertThat($router->getAction(), is('index'));
        assertThat($router->getParams(), is(emptyArray()));
        assertThat($router->getCurrentUrl(), is('?route=home/index'));
        assertThat($router->getImplodedParams(), is(emptyString()));

        $url = $router->create('product', 'care', ['item' => 'mask', 'view' => 'detail']);
        assertThat($url, is('?route=product/care/item/mask/view/detail'));
    }

    public function testRouteProtocolWithSpecificParams(): void
    {
        $router = $this->getRouter([
            RouterConst::NAMESPACE => 'App\\Controller\\',
            RouterConst::CONTROLLER => 'product',
            RouterConst::ACTION => 'save',
            RouterConst::ROUTES => [
                '' => [
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

    public function testRouteProtocolWithSpecificNamesapceAndParams(): void
    {
        $router = $this->getRouter([
            RouterConst::NAMESPACE => 'App\\',
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
        $router = $this->getRouter([
            RouterConst::PROTOCOL => RouterConst::PATH_CLASSNAME,
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
