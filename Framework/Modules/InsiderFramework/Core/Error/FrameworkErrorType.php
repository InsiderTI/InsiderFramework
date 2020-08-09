<?php

namespace Modules\InsiderFramework\Core\Error;

/**
 * FrameworkErrorType type class
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Error\FrameworkErrorType
 */
class FrameworkErrorType
{
    /**
    * Returns all valid framework error types
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\FrameworkErrorType
    *
    * @return array Type of valid framework error types
    */
    public static function getValidFrameworkErrorTypes(): array
    {
        return [
            'CRITICAL',
            'XML_PRE_CONDITION_FAILED',
            'JSON_PRE_CONDITION_FAILED',
            'ATTACK_DETECTED',
            'LOG',
            'WARNING'
        ];
    }
    
    /**
    * Validates the framework error type
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\FrameworkErrorType
    *
    * @param string $frameworkErrorType Framework error type
    *
    * @return bool True if it's valid
    */
    public static function validateFrameworkErrorTypeName(string $frameworkErrorTypeName): bool
    {
        $valid = false;

        if (in_array($frameworkErrorTypeName, FrameworkErrorType::getValidFrameworkErrorTypes())) {
            $valid = true;
        }

        return $valid;
    }
}
