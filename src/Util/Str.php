<?php

namespace Solution10\Data\Util;

abstract class Str
{
    /**
     * @param   string  $string
     * @param   string  $prefix
     * @return  string
     */
    public static function snakeToCamel($string, $prefix = '')
    {
        return lcfirst($prefix.str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $string))));
    }
}
