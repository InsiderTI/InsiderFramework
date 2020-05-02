<?php

namespace Modules\InsiderFramework\Core\Registry\Validation;

/**
 * Validation methods for registry
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Registry\Validation\Registry
 */
trait Registry
{
    /**
     * Validate version syntax
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Registry\Validation\Registry
     *
     * @param string $version Version to be validated
     *
     * @return bool Return of validation
    */
    public static function validateVersionSyntax(string $version): bool
    {
        // If version typed is not valid
        $parts = \Modules\InsiderFramework\Core\Registry::getVersionParts($version);

        // Error
        if ($parts["part1"] === 0 && $parts["part2"] === 0 && $parts["part3"] === 0) {
            return false;
        }
        
        return true;
    }
}
