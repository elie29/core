<?php

declare(strict_types = 1);

namespace Elie\Core\Router\Protocol\Path;

use Elie\Core\Router\RouterConst;
use Elie\Core\Router\Protocol\ProtocolException;
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

    public function testPathWithEmptyRouteThrowsException(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage("'' did not match any routes");

        new Path();
    }

    public function testPathWithUnmatchedRouteThrowsException(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage("'' did not match any routes");

        new Path([
            'home' => [],
            'product/care/*' => [],
        ]);
    }

    public function testPathFromPathInfo(): void
    {
        $_SERVER[RouterConst::PATH_INFO] = 'home';

        $route = new Path([
            'home' => [
                RouterConst::NAMESPACE => 'App\\'
            ],
            'home/*' => [],
        ]);

        assertThat($route->getDefinition(), identicalTo([RouterConst::NAMESPACE => 'App\\']));
    }

    public function testPathFromRequestUri(): void
    {
        $_SERVER[RouterConst::REQUEST_URI] = 'home?query_params';

        $route = new Path([
            'home' => [
                RouterConst::NAMESPACE => 'App\\'
            ]
        ]);

        assertThat($route->getDefinition(), identicalTo([RouterConst::NAMESPACE => 'App\\']));
    }

    public function testPathFromRequestUriStarShoudlMatchWithParams(): void
    {
        $_SERVER[RouterConst::REQUEST_URI] = 'phpunit/phpunit/item/var';

        $route = new Path([
            'phpunit/phpunit' => [
                RouterConst::NAMESPACE => 'App\\'
            ],
            'phpunit/phpunit/*' => [
                RouterConst::NAMESPACE => 'App\\Foo\\'
            ]
        ]);

        assertThat($route->getDefinition(), identicalTo([
            RouterConst::NAMESPACE => 'App\\Foo\\',
            RouterConst::PARAMS => [
                'item' => 'var'
            ]
        ]));
    }

    public function testPathFromRequestUriStarShouldMatchAsFirstRoute(): void
    {
        $_SERVER[RouterConst::REQUEST_URI] = 'phpunit/phpunit';

        $route = new Path([
            'phpunit/phpunit/*' => [
                RouterConst::NAMESPACE => 'App\\'
            ],
            'phpunit/phpunit' => [
                RouterConst::NAMESPACE => 'App\\Foo\\'
            ]
        ]);

        assertThat($route->getDefinition(), identicalTo([
            RouterConst::NAMESPACE => 'App\\',
            RouterConst::PARAMS => [],
        ]));
    }
}
