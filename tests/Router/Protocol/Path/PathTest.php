<?php

declare(strict_types = 1);

namespace Elie\Core\Router\Protocol\Path;

use Elie\Core\Router\Protocol\ProtocolException;
use Elie\Core\Router\RouterConst;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        // $_SERVER affects all tests
        unset($_SERVER[RouterConst::PATH_INFO]);
        unset($_SERVER[RouterConst::REQUEST_URI]);
    }

    protected function tearDown(): void
    {
        unset($_SERVER[RouterConst::PATH_INFO]);
        unset($_SERVER[RouterConst::REQUEST_URI]);

        parent::tearDown();
    }

    public function testPathWithEmptyRoutesShouldFoundNoDefinitions(): void
    {
        $path = new Path();

        assertThat($path->getDefinition(), emptyArray());
    }

    public function testPathWithUnmatchedRoutesShouldFoundNoDefinitions(): void
    {
        $path = new Path([
            'home' => [],
            'product/care/*' => [],
        ]);

        assertThat($path->getDefinition(), emptyArray());
    }

    public function testPathWithNoFoundRoutesExtractPath(): void
    {
        $_SERVER[RouterConst::PATH_INFO] = 'product/care/item/view';

        $path = new Path([
            'home' => [],
            'home/*' => [],
        ]);

        assertThat($path->getDefinition(), hasEntry('controller', 'product'));
        assertThat($path->getDefinition(), equalTo([
            RouterConst::CONTROLLER => 'product',
            RouterConst::ACTION => 'care',
            RouterConst::PARAMS => [
                'item' => 'view'
            ]
        ]));
    }

    public function testPathWithPartialRouteThrowsException(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('URL parts are not set correctly');

        $_SERVER[RouterConst::PATH_INFO] = 'product/care/item/view';

        $path = new Path([
            'home' => [],
            'product/*' => [], // all params after product should be even not odd
        ]);
    }

    public function testPathFromPathInfoWithNoSPecificDefintion(): void
    {
        $_SERVER[RouterConst::PATH_INFO] = 'home';

        $path = new Path([
            'home' => [],
            'home/*' => [],
        ]);

        assertThat($path->getDefinition(), emptyArray());
    }

    public function testPathFromPathInfo(): void
    {
        $_SERVER[RouterConst::PATH_INFO] = 'home';

        $path = new Path([
            'home' => [RouterConst::NAMESPACE => 'App\\'],
            'home/*' => [],
        ]);

        assertThat($path->getDefinition(), identicalTo([RouterConst::NAMESPACE => 'App\\']));
    }

    public function testPathFromRequestUri(): void
    {
        $_SERVER[RouterConst::REQUEST_URI] = 'home?query_params';

        $path = new Path([
            'home' => [RouterConst::NAMESPACE => 'App\\'],
        ]);

        assertThat($path->getDefinition(), identicalTo([RouterConst::NAMESPACE => 'App\\']));
    }

    public function testPathFromRequestUriStarShoudlMatchWithParams(): void
    {
        $_SERVER[RouterConst::REQUEST_URI] = 'phpunit/phpunit/item/var';

        $path = new Path([
            'phpunit/phpunit' => [RouterConst::NAMESPACE => 'App\\'],
            'phpunit/phpunit/*' => [RouterConst::NAMESPACE => 'App\\Foo\\'],
        ]);
        assertThat($path->getDefinition(), identicalTo([
            RouterConst::NAMESPACE => 'App\\Foo\\',
            RouterConst::PARAMS => ['item' => 'var']
        ]));
    }

    public function testPathFromRequestUriStarShouldMatchAsFirstRoute(): void
    {
        $_SERVER[RouterConst::REQUEST_URI] = 'phpunit/phpunit';

        $path = new Path([
            'phpunit/phpunit/*' => [RouterConst::NAMESPACE => 'App\\'],
            'phpunit/phpunit' => [RouterConst::NAMESPACE => 'App\\Foo\\'],
        ]);

        assertThat($path->getDefinition(), identicalTo([
            RouterConst::NAMESPACE => 'App\\',
            RouterConst::PARAMS => [],
        ]));
    }
}
