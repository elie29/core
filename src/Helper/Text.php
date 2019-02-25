<?php

declare(strict_types = 1);

namespace Elie\Core\Helper;

class Text
{

    /**
     * Clean content by stripping newlines and multispaces.
     */
    public static function cleanContent(string $output): string
    {
        // Check for linebreaks
        $output  = str_replace(
            ["\r", "\n", "\t"],
            ' ',
            $output
        );

        // Replace multi spaces by one space
        return preg_replace('/[ ]+/', ' ', $output);
    }

    /**
     * Filter all suspicious characters.
     * <ul>
     *  <li>&#60&#115&#99&#114&#105&#112&#116&#62</li>
     *  <li>&#x3C&#x73&#x63&#x72&#x69&#x70&#x74&#x3E</li>
     *  <li>%3C%73%63%72%69%70%74%3E</li>
     *  <li>%26%23%36%30%26%23%31%31%35%26%23%39%39%26%23%31%31%34%26%23%31%30%35%26%23%31
     *      %31%32%26%23%31%31%36%26%23%36%32: html than hex encoded</li>
     *  <li>%25%33%43%25%37%33%25%36%33%25%37%32%25%36%39%25%37%30%25%37%34%25%33%45: double encoded</li>
     *  <li>\x3c\x73\x63\x72\x69\x70\x74\x3e : evaluated in javascript</li>
     * </ul>
     */
    public static function filter(string $value): string
    {
        $value = self::removeInvisibleChars($value);

        /*
         * Remove:
         *  1. &
         *  2. hexa encoded & (%26)
         *  3. Double encoded hexa (%25)
         *  4. < >
         *  5. \x
         */
        return str_ireplace(
            ['&', '%26', '%25', '%3C', '%3E', '<', '>', '\x'],
            '',
            $value
        );
    }

    /**
     * Remove invisible caracters and HTML tags only.
     * IT DOES NOT REMOVE isolated &, < or >.
     */
    public static function clean(string $value, bool $url_encoded = true): string
    {
        $value = self::removeInvisibleChars($value, $url_encoded);

        return strip_tags($value);
    }

    /**
     * Remove Invisible Characters.
     *
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     *
     * @param string $value String to be cleaned.
     * @param bool $url_encoded If string is encoded or not.
     *
     * @return string
     */
    public static function removeInvisibleChars(string $value, bool $url_encoded = true): string
    {
        $non_displayables = [];
        $count = 0;

        // every control except character newline (dec 10)
        // carriage return (dec 13)
        if ($url_encoded) {
            $non_displayables[] = '/%0[0-9bcef]/i'; // url encoded 00-09, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/i'; // url encoded 16-31
            $non_displayables[] = '/%7f/i'; // url encoded 127
        }
        // No need for i: insensitive
        $non_displayables[] = '/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-09, 11, 12, 14-31, 127

        do {
            $value = preg_replace($non_displayables, '', $value, -1, $count);
        } while ($count);

        return $value;
    }
}
