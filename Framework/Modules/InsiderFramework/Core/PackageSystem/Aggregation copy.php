<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Validation methods for development
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Validation\Aggregation
 */
trait Development
{
    /**
     * Validate parameter
     *
     * @param int    $parameterNumber Parameter order position number
     * @param object $object          Object to be validated
     * @param string $expectedClass   Expected class
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Aggregation
     *
     * @return void
     */
    public static function validateParameter(
        int $parameterNumber,
        $object,
        $expectedClass
    ): void {
        if (!is_subclass_of($object, $expectedClass)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::uncaughtTypeError(
                $parameterNumber,
                $expectedClass,
                get_class($componentObject)
            );
        }
    }
}
