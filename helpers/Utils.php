<?php

namespace ShortenIt\helpers;

class Utils
{

    /**
     * Generate a password passing the format as a parameter
     *
     * ?: Any
     * #: Number
     * .: Letter
     * -: Minus symbol used as a separator
     *
     * @param string $pattern
     * @return string
     */
    public static function generatePassword(string $pattern = '??????-??????-??????'): string
    {
        $ret = '';

        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        str_shuffle($characters);

        for ($i = 0; $i < strlen($pattern); $i++) {
            $ret .= match ($pattern[$i]) {
                '?' => rand(0, 1) == 1 ? rand(0, 9) : $characters[rand(0, strlen($characters) - 1)],
                '#' => rand(0, 9),
                '.' => $characters[rand(0, strlen($characters) - 1)],
                '-' => '-',
            };
        }

        return $ret;
    }

}