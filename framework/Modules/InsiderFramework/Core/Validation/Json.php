<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Validation methods for Json
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Validation\Json
 */
trait Json
{
    /**
     * Checks if a string is a JSON
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Json
     *
     * @param string $value String to be verified
     *
     * @return bool If the string is an JSON, return true
     */
    public static function isJSON(string $value): bool
    {
        // Trying decode the JSON
        $r = json_decode($value);

        // If it is a JSON, returns true
        if ($r !== null) {
            return true;
        }

        return false;
    }
}
