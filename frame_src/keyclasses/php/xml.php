<?php
/**
  KeyClass\XML
*/

namespace KeyClass;

/**
   KeyClass for handling the XML files

   @package KeyClass\XML
   
   @author Marcello Costa
*/
class XML{
    /**
        Converts an object to an XML structured array

        @author Marcello Costa

        @package KeyClass\XML
     
        @param  Array         $data            Data to be converted
        @param  bool|string   $xmlData         Data object to XML
        @param  bool|string   $fixNumericKeys  If it's not false, sets a prefix
                                               for the numeric keys in the XML
     
        @return  SimpleXMLObject  SimpleXML Object
     */
    public static function arrayToXML(Array $data, &$xmlData, $fixNumericKeys=false) : SimpleXMLObject {
        $keys=array_keys($data);

        $root = "root";

        if (!is_object($xmlData)) {
            $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><'.$root.'></'.$root.'>');
        }

        if (count($keys) === 1) {
            $loop[$keys[0]]=$data[$keys[0]];
        }
        else {
            $loop=$data;
        }

        foreach($loop as $key => $value) {
            if (is_numeric($key)) {
                if ($fixNumericKeys !== false && $fixNumericKeys !== null) {
                    $key = (string)$fixNumericKeys.$key;
                }
                else {
                    \KeyClass\Error::i10nErrorRegister("Error converting Array to XML: numeric keys were encountered", 'pack/sys');
                }
            }
            if (is_array($value)) {
                $subnode = $xmlData->addChild($key);
                \KeyClass\XML::arrayToXML($value, $subnode, $fixNumericKeys);
            }
            else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }

        return $xmlData;
    }

    /**
        Checks if a string is a XML
     
        @author Marcello Costa

        @package KeyClass\XML
     
        @param  string  $xmlstr    String to be verified
     
        @return  bool  If it's a XML returns true
    */
    public static function isXML(string $xmlstr) : bool {
        libxml_use_internal_errors(true);
        simplexml_load_string($xmlstr);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if (count($errors) === 0) {
            return true;
        }
        else {
            return false;
        }
    }
}
