<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods for numbers
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Manipulation\Number
 */
trait Number
{
    /**
     * Returns a numeric variable
     *
     * @author Marcello Costa <marcello88costa@yahoo.com.br>
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Number
     *
     * @param string $value String to be tested
     *
     * @return int|float Returns the numeric variable
     */
    public static function getNumeric(string $value)
    {
        if (is_numeric($value)) {
            return $value + 0;
        }

        return false;
    }
}
