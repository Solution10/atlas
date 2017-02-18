<?php

namespace Solution10\Data\Parts;

/**
 * Trait GetDateTime
 *
 * Helper trait for working with DateTime's (converting between strings and
 * integers into creating DT objects).
 *
 * @package     Solution10\Data\Parts
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait GetDateTime
{
    /**
     * Takes an input (string, integer or \DateTime) and returns a \DateTime
     * object for that input, or null if it isn't recognised.
     *
     * @param   mixed   $input
     * @return  \DateTime|null
     */
    protected function getDateTimeFrom($input)
    {
        $return = $input;
        if ($input instanceof \DateTime === false) {
            if (is_integer($input)) {
                $return = new \DateTime();
                $return->setTimestamp($input);
            } else {
                $return = new \DateTime($input);
            }
        }
        return $return;
    }
}
