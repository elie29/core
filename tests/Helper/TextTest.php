<?php

declare(strict_types = 1);

namespace Elie\Core\Helper;

use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{

    /**
     * @dataProvider cleanOutputProvider
     */
    public function testCleanOutput(string $output, string $expected): void
    {
        assertThat(Text::cleanContent($output), identicalTo($expected));
    }

    public function cleanOutputProvider(): \Generator
    {
        yield 'simple plain text' => [
            // output
            'with multi   space',
            // expected
            'with multi space'
        ];

        yield 'text with eol' => [
            // output
            "with multi   \n\r\n\r\r\nspace " . PHP_EOL,
            // expected
            'with multi space '
        ];
    }
}
