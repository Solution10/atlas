<?php

namespace Solution10\Data;

/**
 * Trait StringConverter
 *
 * Some string utilities
 *
 * @package     Solution10\Data
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait StringConverter
{
    /**
     * @param   string  $string
     * @param   string  $prefix
     * @return  string
     */
    public function snakeToCamel(string $string, string $prefix = ''): string
    {
        return lcfirst($prefix.str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $string))));
    }
}
