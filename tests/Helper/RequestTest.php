<?php

declare(strict_types = 1);

namespace Elie\Core\Helper;

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{

    public function testIsAjax(): void
    {
        assertThat(Request::isAjax(), is(false));

        $_SERVER[Request::HTTP_X_REQUESTED_WITH] = 'with/ajax';

        assertThat(Request::isAjax(), is(true));
    }

    public function testHasReferer(): void
    {
        assertThat(Request::hasReferer('localhost'), is(false));

        $_SERVER[Request::HTTP_REFERER] = 'http://localhost/index.php';

        assertThat(Request::hasReferer('localhost'), is(false));
        assertThat(Request::hasReferer('http://localhost/'), is(true));
        assertThat(Request::hasReferer('http://localhost'), is(true));
    }
}
