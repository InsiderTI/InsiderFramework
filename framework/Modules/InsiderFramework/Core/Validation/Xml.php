<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Methods for Xml
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Validation\Xml
 */
trait Xml
{
    /**
     * Checks if a string is a XML
     *
     * @author Marcello Costa <marcello88costa@yahoo.com.br>
     *
     * @package Modules\InsiderFramework\Core\Validation\Xml
     *
     * @param string $xmlstr String to be verified
     * 
     * @return bool If it's a XML returns true
    */
    public static function isXML(string $xmlstr): bool
    {
        libxml_use_internal_errors(true);
        simplexml_load_string($xmlstr);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if (count($errors) === 0) {
            return true;
        } else {
            return false;
        }
    }
}
