<?php

declare(strict_types = 1);

namespace Elie\Core;

use Elie\Core\Router\RouterInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Elie\Core\Render\RenderInterface;

class CoreTest extends TestCase
{

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function testCoreClassNotExists(): void
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('Class not found namespaceHomeIndexController');

        $router = \Mockery::mock(RouterInterface::class);
        $router->expects('getNamespace')->andReturn('namespace');
        $router->expects('getController')->andReturn('home');
        $router->expects('getAction')->andReturn('index');

        $container = \Mockery::mock(ContainerInterface::class);
        $container->expects('get')->with(RouterInterface::class)->andReturn($router);

        $core = new Core($container);
        $core->run();
    }

    public function testCore(): void
    {
        $render = \Mockery::mock(RenderInterface::class);
        $render->expects('fetchLayout')->andReturn('<div>');
        $render->expects('fetchTemplate')->andReturn('item');

        $router = \Mockery::mock(RouterInterface::class);
        $router->expects('getNamespace')->andReturn('App\\Controller\\');
        $router->expects('getController')->andReturn('home');
        $router->shouldReceive('getAction')->twice()->andReturn('index');
        $router->expects('getParams')->andReturn([]);

        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(RouterInterface::class)->twice()->andReturn($router);
        $container->shouldReceive('get')->with(RenderInterface::class)->twice()->andReturn($render);

        $core = new Core($container);

        ob_start();

        $core->run();

        $content = ob_get_clean();

        assertThat($content, is('<div>'));
    }
}
