<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods for Xml
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Manipulation\Xml
 */
trait Xml
{
    /**
     * Converts an object to an XML structured array
     *
     * @author Marcello Costa <marcello88costa@yahoo.com.br>
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Xml
     *
     * @param array       $data           Data to be converted
     * @param bool|string $xmlData        Data object to XML
     * @param bool|string $fixNumericKeys If it's not false, sets a prefix
     *                                    for the numeric keys in the XML
     * @return SimpleXMLObject SimpleXML Object
     */
    public static function arrayToXML(
        array $data,
        &$xmlData,
        $fixNumericKeys = false
    ): SimpleXMLObject {
        $keys = array_keys($data);

        $root = "root";

        if (!is_object($xmlData)) {
            $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><' . $root . '></' . $root . '>');
        }

        if (count($keys) === 1) {
            $loop[$keys[0]] = $data[$keys[0]];
        } else {
            $loop = $data;
        }

        foreach ($loop as $key => $value) {
            if (is_numeric($key)) {
                if ($fixNumericKeys !== false && $fixNumericKeys !== null) {
                    $key = (string)$fixNumericKeys . $key;
                } else {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        "Error converting Array to XML: numeric keys were encountered",
                        "app/sys"
                    );
                }
            }
            if (is_array($value)) {
                $subnode = $xmlData->addChild($key);
                \Modules\InsiderFramework\Core\Xml::arrayToXML($value, $subnode, $fixNumericKeys);
            } else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }

        return $xmlData;
    }
}
