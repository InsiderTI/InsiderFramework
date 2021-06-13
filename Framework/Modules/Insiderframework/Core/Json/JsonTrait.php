<?php

namespace Modules\Insiderframework\Core\Json;

/**
 * Methods responsible for handle JSON
 *
 * @author  Marcello Costa
 *
 * @package Modules\Insiderframework\Core\Json\JsonTrait
 */
trait JsonTrait
{
    /**
     * Get the data of a JSON file
     *
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\Json\JsonTrait
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
            $filecontent = \Modules\Insiderframework\Core\FileTree::fileReadContent($filepath);

            $t = json_decode($filecontent);

            if ($t === null) {
                return false;
            }

            return (json_decode($filecontent, $assoc));
        }
        
        return false;
    }
}