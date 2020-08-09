<?php

namespace Modules\InsiderFramework\Core\Error;

/**
 * Error fatal class
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Error\ErrorFatal
 */
class ErrorFatal
{
    /**
    * Returns the number of valid php error numbers
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\ErrorFatal
    *
    * @return array PHP error level numbers
    */
    public static function getListOfValidPhpErrorNumbers(): array
    {
        return [
            1,2,4,8,16,32,64,128,256,512,1024,2048,4096,8192,16384,32767
        ];
    }

    /**
    * Get if error is fatal by error number
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\ErrorFatal
    *
    * @param int $errorNumber Error number
    *
    * @return bool True if error is fatal
    */
    public static function getIfErrorIsFatalByErrorNumber($errorNumber): bool
    {
        if (!in_array($errorNumber, ErrorFatal::getListOfValidPhpErrorNumbers())) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                'Unknow PHP error type/level number: ' . $errorNumber
            );
        }

        $fatal = false;

        switch ($errorNumber) {
            case 1:
                $fatal = true;
                break;

            case 4:
                $fatal = true;
                break;

            case 16:
                $fatal = true;
                break;


            case 64:
                $fatal = true;
                break;

            
            case 4096:
                $fatal = true;
                break;
        }

        return $fatal;
    }
}
