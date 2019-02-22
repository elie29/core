<?php

declare(strict_types = 1);

namespace Elie\Core\Router\Protocol\Query;

use Elie\Core\Router\RouterConst;
use Elie\Core\Router\Protocol\ProtocolException;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        // $_GET affects all tests
        unset($_GET);
    }

    protected function tearDown(): void
    {
        unset($_GET);

        parent::tearDown();
    }

    public function testQueryWithEmptyRouteThrowsException(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage("'' did not match any routes");

        new Query();
    }

    public function testQueryWithUnmatchedRouteThrowsException(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage("'' did not match any routes");

        new Query([
            'home' => [],
            'product/care/*' => [],
        ]);
    }

    public function testQueryShouldMatchFirstRouteWithNamespace(): void
    {
        $_GET[RouterConst::ROUTE] = 'home';

        $route = new Query([
            'home' => [
                RouterConst::NAMESPACE => 'App\\'
            ],
            'home/*' => [],
        ]);

        assertThat($route->getDefinition(), identicalTo([RouterConst::NAMESPACE => 'App\\']));
    }

    public function testQueryShouldMatchFirstRouteWithoutNamespace(): void
    {
        $_GET[RouterConst::ROUTE] = 'home';

        $route = new Query([
            'home/*' => [], // first route found -> defintions contains params => []
            'home' => [
                RouterConst::NAMESPACE => 'App\\'
            ],
        ]);

        assertThat($route->getDefinition(), identicalTo([RouterConst::PARAMS => []]));
    }

    public function testQueryWithStarRouteThrowsException(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('URL parts are not set correctly');

        $_GET[RouterConst::ROUTE] = 'home/item/'; // params should be even

        $route = new Query([
            'home/*' => []
        ]);
    }

    public function testQueryWithStarRoute(): void
    {
        $_GET[RouterConst::ROUTE] = 'home/item/care';

        $route = new Query([
            'home/*' => [], // first route found -> params should be set to item=>care
            'home' => [
                RouterConst::NAMESPACE => 'App\\'
            ],
        ]);

        assertThat($route->getDefinition(), identicalTo([RouterConst::PARAMS => [
            'item' => 'care'
        ]]));
    }

    public function testQueryWithStarRouteWithMergedParams(): void
    {
        $_GET[RouterConst::ROUTE] = 'home/item/care';

        $route = new Query([
            'home/*' => [
                RouterConst::PARAMS => [
                    'view' => 'detail'
                ]
            ]
        ]);

        assertThat($route->getDefinition(), identicalTo([RouterConst::PARAMS => [
            'view' => 'detail',
            'item' => 'care',
        ]]));
    }

    public function testQueryWithStarRouteWithSameKeyMergedParams(): void
    {
        $_GET[RouterConst::ROUTE] = 'home/item/care';

        $route = new Query([
            'home/*' => [
                RouterConst::PARAMS => [
                    'item' => 'detail'
                ]
            ]
        ]);

        assertThat($route->getDefinition(), identicalTo([RouterConst::PARAMS => [
            'item' => 'care',
        ]]));
    }
}
