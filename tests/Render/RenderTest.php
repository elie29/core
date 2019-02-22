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

    protected function tearDown(): void
    {
        \Mockery::close();

        if ($this->render instanceof RenderInterface) {
            $this->render->cleanCachedFile();
        }

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
        assertThat($this->render->hasExpired(), is(true));
    }

    public function testRenderWithCacheConfig(): void
    {
        $this->setRender(true);

        // File does not exists
        assertThat($this->render->hasExpired(), is(true));

        $this->render->assign(['product' => 'milk']);
        $data = $this->render->fetchLayout(['item' => 'honey']);

        assertThat($data, containsString('https://test.com/home/index'));
        assertThat($data, containsString('<div>honey</div>'));
        assertThat($data, containsString('<div>milk</div>'));

        assertThat($this->render->hasExpired(), is(false));
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

    public function testRenderUnexistantTemplateThrowsException(): void
    {
        $this->expectException(RenderException::class);
        $this->expectExceptionMessage('Can\'t find view script');

        $this->setRender(false);

        $data = $this->render->fetchTemplate(['item' => 'honey'], 'product/test');
    }

    public function testRenderDefaultTemplate(): void
    {
        $this->setRender(false);

        // default template: home/index
        $data = $this->render->fetchTemplate(['item' => 'honey']);

        assertThat($data, containsString('<div>honey</div>'));
    }

    public function testRenderDefaultTemplateWithoutRequiredData(): void
    {
        $this->setRender(false);

        // default template: home/index
        $data = $this->render->fetchTemplate();

        assertThat($data, containsString('item is not set correctly'));
    }

    public function testRenderViewTwiceWithCache(): void
    {
        // layout cache could be deactivated
        $this->setRender(false);

        // set template cache time to 2 seconds
        $this->render->setTemplateCacheTime(2);

        $data1 = $this->render->fetchTemplate(['item' => 'honey'], 'products/care');
        $data2 = $this->render->fetchTemplate(['item' => 'honey'], 'products/care');

        assertThat($data1, is($data2));
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
