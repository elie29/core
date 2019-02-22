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
}
