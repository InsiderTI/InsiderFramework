<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods responsible for handle JSON
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @link    https://www.insiderframework.com/documentation/keyclass#json
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\Manipulation\Json
 */
trait Json
{
    /**
     * Get the data of a JSON file
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Json
     *
     * @param string $filepath Path of the JSON file
     * @param bool   $assoc    If this is true the function will return
     *                         an associative array instead of an object
     *
     * @return array|bool Data of JSON file if the file can be read.
     *                    If not, returns false.
     */
    public static function getJSONDataFile(string $filepath, bool $assoc = true)
    {
        if (file_exists($filepath)) {
            // Getting the content of the file
            $filecontent = \Modules\InsiderFramework\Core\FileTree::fileReadContent($filepath);

            $t = json_decode($filecontent);

            if ($t === null) {
                return false;
            }

            // Retuning the data
            return (json_decode($filecontent, $assoc));
        } else {
            return false;
        }
    }

    /**
     * Records data to a JSON file
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Json
     *
     * @param mixed  $data      Data to be recorded
     * @param string $filepath  Path of the JSON file
     * @param bool   $overwrite If this is true, overwrites the data of JSON file
     *
     * @return bool Processing result
     */
    public static function setJSONDataFile($data, string $filepath, bool $overwrite = false): bool
    {
        // Encoding the data
        $datafile = \Modules\InsiderFramework\Core\Json::jsonEncodePrivateObject($data);

        // Recording the content in the file
        $return = \Modules\InsiderFramework\Core\FileTree::fileWriteContent($filepath, $datafile, $overwrite);

        if ($return === false) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("Unable to write to file: " . $filepath);
        }

        return true;
    }

    /**
     * Function that extract the private properties of an object and
     * return this properties as a JSON string
     *
     * @author Marcello Costa
     * @author Petah
     * @author Andre Medeiros
     * @see    https://stackoverflow.com/questions/7005860/php-json-encode-class-private-members
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Json
     *
     * @param object $object Object that will be readed/extracted
     *
     * @return string String that represents the object
     */
    public static function jsonEncodePrivateObject($object): string
    {
        if (is_object($object)) {
            return json_encode(\Modules\InsiderFramework\Core\Json::extractObjectPrivateProps($object));
        } else {
            return json_encode($object);
        }
    }

    /**
     * Function that extract the private properties of an object
     *
     * @author Marcello Costa
     * @author Petah
     * @author Andre Medeiros
     * @see    https://stackoverflow.com/questions/7005860/php-json-encode-class-private-members
     *
     * @package Modules\InsiderFramework\Core\Json
     *
     * @param object $object Object that will be readed/extracted
     *
     * @return array|bool Array that represents the object or false
     *                    (if it is not an object)
     */
    public static function extractObjectPrivateProps($object): array
    {
        $public = [];

        if (!is_object($object)) {
            return false;
        }

        $reflection = new \ReflectionClass(get_class($object));

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            $value = $property->getValue($object);
            $name = $property->getName();

            if (is_array($value)) {
                $public[$name] = [];

                foreach ($value as $item) {
                    if (is_object($item)) {
                        $itemArray = \Modules\InsiderFramework\Core\Json::extractObjectPrivateProps($item);

                        $public[$name][] = $itemArray;
                    } else {
                        $public[$name][] = $item;
                    }
                }
            } elseif (is_object($value)) {
                $public[$name] = \Modules\InsiderFramework\Core\Json::extractObjectPrivateProps($value);
            } else {
                $public[$name] = $value;
            }
        }

        return $public;
    }
}
