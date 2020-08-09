<?php

namespace Modules\InsiderFramework\Core\Error;

/**
 * Error type class
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Error\PhpErrorType
 */
class PhpErrorType
{
    /**
    * Returns all valid php error types
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\PhpErrorType
    *
    * @return array Type of valid php error types
    */
    public static function getValidPhpErrorTypes(): array
    {
        return [
            'E_ERROR',
            'E_WARNING',
            'E_PARSE',
            'E_NOTICE',
            'E_CORE_ERROR',
            'E_CORE_WARNING',
            'E_COMPILE_ERROR',
            'E_COMPILE_WARNING',
            'E_USER_ERROR',
            'E_USER_WARNING',
            'E_USER_NOTICE',
            'E_STRICT',
            'E_RECOVERABLE_ERROR',
            'E_DEPRECATED',
            'E_USER_DEPRECATED'
        ];
    }
    
    /**
    * Get PHP error type name by error number
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\PhpErrorType
    *
    * @param int $errorNumber Error number
    *
    * @return string Error name
    */
    public static function getPhpErrorTypeByErrorNumber(int $errorNumber): string
    {
        $errorType = "";
        switch ($errorNumber) {
            case 1:
                $errorType = 'E_ERROR';
                $fatal = true;
                break;

            case 2:
                $errorType = 'E_WARNING';
                break;

            case 4:
                $errorType = 'E_PARSE';
                $fatal = true;
                break;

            case 8:
                $errorType = 'E_NOTICE';
                break;

            case 16:
                $errorType = 'E_CORE_ERROR';
                $fatal = true;
                break;

            case 32:
                $errorType = 'E_CORE_WARNING';
                break;

            case 64:
                $errorType = 'E_COMPILE_ERROR';
                $fatal = true;
                break;

            case 128:
                $errorType = 'E_COMPILE_WARNING';
                break;

            case 256:
                $errorType = 'E_USER_ERROR';
                break;

            case 512:
                $errorType = 'E_USER_WARNING';
                break;

            case 1024:
                $errorType = 'E_USER_NOTICE';
                break;

            case 2048:
                $errorType = 'E_STRICT';
                break;

            case 4096:
                $errorType = 'E_RECOVERABLE_ERROR';
                $fatal = true;
                break;

            case 8192:
                $errorType = 'E_DEPRECATED';
                break;

            case 16384:
                $errorType = 'E_USER_DEPRECATED';
                break;

            default:
                \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                    'Unknow error type number: ' . $errorNumber
                );
                break;
        }

        return $errorType;
    }

    /**
    * Validate an php error type name
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\PhpErrorType
    *
    * @param string $errorTypeName Error type name
    *
    * @return bool True if it's a valid error type name
    */
    public static function validatePhpErrorTypeName(string $phpErrorTypeName): bool
    {
        $valid = false;

        if (in_array($phpErrorTypeName, PhpErrorType::getValidPhpErrorTypes())) {
            $valid = true;
        }
        return $valid;
    }
}
