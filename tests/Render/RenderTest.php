<?php

declare(strict_types = 1);

namespace Elie\Core\Render;

use Elie\Core\Router\RouterInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class RenderTest extends TestCase
{

    /**
     * @var RenderInterface
     */
    protected $render;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        \Mockery::close();

        parent::tearDown();
    }

    public function testRenderWithEmptyConfig(): void
    {
        $router = \Mockery::mock(RouterInterface::class);

        $container = \Mockery::mock(ContainerInterface::class);
        $container->expects('get')->with('config')->andReturn([]);
        $container->expects('get')->with(RouterInterface::class)->andReturn($router);

        $this->render = new Render($container);

        // cache should be deactivated
        assertThat($this->render->hasLayoutExpired(), is(true));
    }

    public function testRenderWithCacheConfig(): void
    {
        $this->setRender(true);

        // File does not exists
        assertThat($this->render->hasLayoutExpired(), is(true));

        // Global data
        $this->render->assign(['description' => 'milk']);

        // Local data
        $data = [
            'description' => 'product', // won't override
            'item' => 'honey',
            'uri' => 'home/index',
        ];

        $content = $this->render->fetchLayout($data);

        assertThat($content, containsString('https://test.com/home/index'));
        assertThat($content, containsString('<div>honey</div>'));
        assertThat($content, containsString('<div>milk</div>'));

        assertThat($this->render->hasLayoutExpired(), is(false));

        // Read content fromc cache
        $content = $this->render->fetchLayout();
        assertThat($content, containsString('<div>milk</div>'));

        // delete cache file
        @unlink('tests/app/cache/layouts-layout.home.index');
    }

    public function testRenderJsonData(): void
    {
        $this->setRender(false);

        $this->render->jsonRendering();
        $this->render->assign(['product' => 'milk']);
        $data = $this->render->fetchLayout(['item' => 'honey']);

        assertThat($data, is('{"item":"honey","product":"milk"}'));
    }

    public function testRenderTextData(): void
    {
        $this->setRender(false);

        $this->render->textRendering();
        $this->render->assign(['product' => 'milk']);
        $data = $this->render->fetchLayout(['item' => 'honey']);

        assertThat($data, is("honey\nmilk"));
    }

    public function testRenderUnexistantLayoutThrowsException(): void
    {
        $this->expectException(RenderException::class);
        $this->expectExceptionMessage('Can\'t find view script');

        $this->setRender(false);

        $this->render->changeLayout('layout-unexists');

        $this->render->fetchLayout();
    }

    public function testRenderUnexistantTemplateThrowsException(): void
    {
        $this->expectException(RenderException::class);
        $this->expectExceptionMessage('Can\'t find view script');

        $this->setRender(false);

        $this->render->fetchTemplate([], 'product/test');
    }

    public function testRenderDefaultTemplate(): void
    {
        $this->setRender(false);

        // default template: home/index
        $data = $this->render->fetchTemplate([
            'action' => 'index',
            'item' => 'honey'
        ]);

        assertThat($data, containsString('<div>honey</div>'));
    }

    public function testRenderDefaultTemplateWithAssignedData(): void
    {
        $this->setRender(false);

        $this->render->assign(['item' => 'tada']);

        // default template: home/index
        $data = $this->render->fetchTemplate([
            'action' => 'index',
            'item' => 'honey'
        ]);

        assertThat($data, containsString('<div>tada</div>'));
    }

    public function testRenderViewTwiceWithCache(): void
    {
        // without layout cache
        $this->setRender(false);

        $cacheFile = 'cacheFileNameID'; // Should be unique.
        $cacheTime = 2;

        // Cachetime should be >= 0
        assertThat($this->render->hasTemplateExpired($cacheFile, -1), is(true));

        $content1 = $this->render->fetchTemplate(['item' => 'honey'], 'products/care', $cacheFile, $cacheTime);
        $content2 = $this->render->fetchTemplate([], null, $cacheFile, $cacheTime);

        assertThat($content1, is($content2));
        assertThat($this->render->hasTemplateExpired($cacheFile, $cacheTime), is(false));

        @unlink('tests/app/cache/' . $cacheFile);
    }

    protected function setRender(bool $withCache): void
    {
        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('getController')->andReturn('home');
        $router->shouldReceive('getAction')->andReturn('index');
        $router->shouldReceive('getImplodedParams')->andReturn('');
        $router->shouldReceive('getUrlPath')->andReturn('home/index');

        $config = [
            'core' => [
                'render' => [
                    RenderConst::LAYOUT => 'layouts/layout', // under app/views
                    RenderConst::CLEAN_OUTPUT => true,
                    RenderConst::CACHE_TIME => $withCache ? 5 : -1, // 5 seconds
                    RenderConst::CACHE_PATH => dirname(__DIR__) . '/app/cache',
                    RenderConst::VIEWS_PATH => dirname(__DIR__) . '/app/views',
                ]
            ]
        ];

        $container = \Mockery::mock(ContainerInterface::class);
        $container->expects('get')->with('config')->andReturn($config);
        $container->expects('get')->with(RouterInterface::class)->andReturn($router);

        $this->render = new Render($container);
    }
}
