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

    /**
     * @dataProvider removeInvisibleCharactersProvider
     */
    public function testRemoveInvisibleCharacters(string $value, bool $encoded, string $expected): void
    {
        assertThat(Text::removeInvisibleChars($value, $encoded), identicalTo($expected));
    }

    /**
     * @dataProvider filterProvider
     */
    public function testFilter(string $value, string $expected): void
    {
        assertThat(Text::filter($value), identicalTo($expected));
    }

    /**
     * @dataProvider cleanProvider
     */
    public function testClean(string $value, bool $encoded, string $expected): void
    {
        assertThat(Text::clean($value, $encoded), identicalTo($expected));
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

    public function removeInvisibleCharactersProvider(): \Generator
    {
        yield 'encoded invisible characters' => [
            '%7F%0f%2F%1F%1b%0B%7f',
            true,
            '%2F'
        ];

        yield 'invisible characters' => [
            "\x00\x08\x0F\x1e\x1E\x7f\x0b\x0C\xeE",
            false,
            "\xeE"
        ];

        yield 'mixed encoded invisible characters' => [
            "\x00\x08\x0F%0f%2F%1F%1b%0B\x1e\x1E\x7f\x0b\x0C\x3f",
            true,
            "%2F\x3f"
        ];

        yield 'sandwishing script' => [
            "Java\0script",
            false,
            "Javascript"
        ];
    }

    public function filterProvider(): \Generator
    {
        yield 'filter script' => [
            '<script>',
            'script'
        ];

        yield 'html encoded <script>' => [
            '&#60&#115&#99&#114&#105&#112&#116&#62',
            '#60#115#99#114#105#112#116#62'
        ];

        yield 'html encoded <script>' => [
            '&#x3C&#x73&#x63&#x72&#x69&#x70&#x74&#x3E',
            '#x3C#x73#x63#x72#x69#x70#x74#x3E'
        ];

        yield 'invisible characters' => [
            "\x3c\x73\x63\x72\x69\x70\x74\x3e",
            'script'
        ];
    }

    public function cleanProvider(): \Generator
    {
        yield 'encoded invisible characters' => [
            "\x3c\x73\x63\x72\x69\x70\x74\x3e<script>alert</script>",
            true,
            'alert'
        ];
    }
}
