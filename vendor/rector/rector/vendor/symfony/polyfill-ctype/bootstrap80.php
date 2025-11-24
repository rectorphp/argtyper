<?php

namespace Argtyper202511;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Symfony\Polyfill\Ctype as p;
if (!\function_exists('ctype_alnum') && !\function_exists('Argtyper202511\ctype_alnum')) {
    /**
     * @param mixed $text
     */
    function ctype_alnum($text): bool
    {
        return p\Ctype::ctype_alnum($text);
    }
}
if (!\function_exists('ctype_alpha') && !\function_exists('Argtyper202511\ctype_alpha')) {
    /**
     * @param mixed $text
     */
    function ctype_alpha($text): bool
    {
        return p\Ctype::ctype_alpha($text);
    }
}
if (!\function_exists('ctype_cntrl') && !\function_exists('Argtyper202511\ctype_cntrl')) {
    /**
     * @param mixed $text
     */
    function ctype_cntrl($text): bool
    {
        return p\Ctype::ctype_cntrl($text);
    }
}
if (!\function_exists('ctype_digit') && !\function_exists('Argtyper202511\ctype_digit')) {
    /**
     * @param mixed $text
     */
    function ctype_digit($text): bool
    {
        return p\Ctype::ctype_digit($text);
    }
}
if (!\function_exists('ctype_graph') && !\function_exists('Argtyper202511\ctype_graph')) {
    /**
     * @param mixed $text
     */
    function ctype_graph($text): bool
    {
        return p\Ctype::ctype_graph($text);
    }
}
if (!\function_exists('ctype_lower') && !\function_exists('Argtyper202511\ctype_lower')) {
    /**
     * @param mixed $text
     */
    function ctype_lower($text): bool
    {
        return p\Ctype::ctype_lower($text);
    }
}
if (!\function_exists('ctype_print') && !\function_exists('Argtyper202511\ctype_print')) {
    /**
     * @param mixed $text
     */
    function ctype_print($text): bool
    {
        return p\Ctype::ctype_print($text);
    }
}
if (!\function_exists('ctype_punct') && !\function_exists('Argtyper202511\ctype_punct')) {
    /**
     * @param mixed $text
     */
    function ctype_punct($text): bool
    {
        return p\Ctype::ctype_punct($text);
    }
}
if (!\function_exists('ctype_space') && !\function_exists('Argtyper202511\ctype_space')) {
    /**
     * @param mixed $text
     */
    function ctype_space($text): bool
    {
        return p\Ctype::ctype_space($text);
    }
}
if (!\function_exists('ctype_upper') && !\function_exists('Argtyper202511\ctype_upper')) {
    /**
     * @param mixed $text
     */
    function ctype_upper($text): bool
    {
        return p\Ctype::ctype_upper($text);
    }
}
if (!\function_exists('ctype_xdigit') && !\function_exists('Argtyper202511\ctype_xdigit')) {
    /**
     * @param mixed $text
     */
    function ctype_xdigit($text): bool
    {
        return p\Ctype::ctype_xdigit($text);
    }
}
