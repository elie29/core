<?php

declare(strict_types = 1);

namespace Elie\Core\Router\Protocol\Query;

use Elie\Core\Router\Protocol\ProtocolException;
use Elie\Core\Router\RouterConst;
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

    public function testQueryWithEmptyRouteShouldHaveEmptyDefintions(): void
    {
        $query = new Query();

        assertThat($query->getDefinition(), emptyArray());
    }

    public function testPathWithUnmatchedRoutesShouldFoundNoDefinitions(): void
    {
        $query = new Query([
            'home' => [],
            'product/care/*' => [],
        ]);

        assertThat($query->getDefinition(), emptyArray());
    }

    public function testPathWithNoFoundRoutesExtractPath(): void
    {
        $_GET[RouterConst::ROUTE] = 'product/care/item/view';

        $query = new Query([
            'home' => [],
            'home/*' => [],
        ]);

        assertThat($query->getDefinition(), equalTo([
            RouterConst::CONTROLLER => 'product',
            RouterConst::ACTION => 'care',
            RouterConst::PARAMS => [
                'item' => 'view'
            ]
        ]));
    }

    public function testQueryShouldMatchFirstRouteWithNamespace(): void
    {
        $_GET[RouterConst::ROUTE] = 'home';

        $query = new Query([
            'home' => [RouterConst::NAMESPACE => 'App\\'],
            'home/*' => [],
        ]);

        assertThat($query->getDefinition(), identicalTo([RouterConst::NAMESPACE => 'App\\']));
    }

    public function testQueryShouldMatchFirstRouteWithoutNamespace(): void
    {
        $_GET[RouterConst::ROUTE] = 'home';

        $query = new Query([
            'home/*' => [], // first route found -> defintions contains params => []
            'home' => [RouterConst::NAMESPACE => 'App\\'],
        ]);

        assertThat($query->getDefinition(), identicalTo([RouterConst::PARAMS => []]));
    }

    public function testQueryWithStarRouteThrowsException(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('URL parts are not set correctly');

        $_GET[RouterConst::ROUTE] = 'home/item/'; // params should be even

        $query = new Query([
            'home/*' => []
        ]);
    }

    public function testQueryWithStarRoute(): void
    {
        $_GET[RouterConst::ROUTE] = 'home/item/care';

        $query = new Query([
            'home/*' => [], // first route found -> params should be set to item=>care
            'home' => [RouterConst::NAMESPACE => 'App\\'],
        ]);

        assertThat($query->getDefinition(), identicalTo([RouterConst::PARAMS => [
            'item' => 'care'
        ]]));
    }

    public function testQueryWithStarRouteWithMergedParams(): void
    {
        $_GET[RouterConst::ROUTE] = 'home/item/care';

        $query = new Query([
            'home/*' => [
                RouterConst::PARAMS => [
                    'view' => 'detail'
                ]
            ]
        ]);

        assertThat($query->getDefinition(), identicalTo([RouterConst::PARAMS => [
            'view' => 'detail',
            'item' => 'care',
        ]]));
    }

    public function testQueryWithStarRouteWithSameKeyMergedParams(): void
    {
        $_GET[RouterConst::ROUTE] = 'home/item/care';

        $query = new Query([
            'home/*' => [
                RouterConst::PARAMS => [
                    'item' => 'detail'
                ]
            ]
        ]);

        assertThat($query->getDefinition(), identicalTo([RouterConst::PARAMS => [
            'item' => 'care',
        ]]));
    }
}
