<?php

namespace Modules\Insiderframework\Core\FileTree;

/**
 * Methods responsible for handle files and directories
 *
 * @author Marcello Costa
 *
 * @package Modules\Insiderframework\Core\FileTree\FileTreeTrait
 *
 */
trait FileTreeTrait
{
    /**
     * Gets the content of an file
     *
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\FileTree\FileTreeTrait
     *
     * @param string    $filepath          Path of the file
     * @param bool      $returnstring      If true, the function will return a string, otherwise will return an array
     * @param int|float $delaytry          Time (in seconds) between the read attempts
     * @param int       $maxToleranceLoops Maximum loop number waiting the $delaytry time
     *
     * @return string|array Content of the file
     */
    public static function fileReadContent(
        string $filepath,
        bool $returnstring = true,
        $delaytry = 0.15,
        int $maxToleranceLoops = null
    ) {
        if (!is_numeric($delaytry)) {
            \Modules\Insiderframework\Core\Error::primaryError('Variable delaytry is not numeric');
        }

        if ($maxToleranceLoops === null) {
            if (!defined("MAX_TOLERANCE_LOOPS")) {
                $maxToleranceLoops = 1000;
            } else {
                $maxToleranceLoops = MAX_TOLERANCE_LOOPS;
            }
        }

        if ($filepath === null) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The file path not specified in \Modules\Insiderframework\Core\FileTree\FileTreeTrait::fileReadContent()"
            );
        }

        $countToleranceLoops = 0;
        $idError = null;

        while (file_exists($filepath . ".lock")) {
            $maxToleranceLoops++;
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
                $countToleranceLoops = 0;

                \Modules\Insiderframework\Core\Error::primaryError(
                  "Too long waiting time detected to read file: " . $filepath
                );
            }

            sleep($delaytry);
        }

        if (!file_exists($filepath) || !is_readable($filepath)) {
            return false;
        }

        if ($returnstring === true) {
            $result = file_get_contents($filepath);
        } else {
            $result = file($filepath);
        }

        if ($result !== false) {
            return $result;
        }

        return false;
    }

    /**
     * Creates a map in format of an array of a path
     *
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\FileTree
     *
     * @param string $dir       Path to be mapped
     * @param bool   $sortitems Splits the result inside the array ordering by directories
     *
     * @return array Array of the mapped path
     */
    public static function dirTree(string $dir, bool $sortitems = false): array
    {
        // Removing the last bar from the string $dir (if did exists)
        if ($dir[strlen($dir) - 1] === DIRECTORY_SEPARATOR) {
            $dir = \Modules\InsiderFramework\Core\Text::extractString($dir, 0, strlen($dir) - 1);
        }

        // Mapping the directory
        $path = [];
        $stack[] = $dir;
        while ($stack) {
            $thisdir = array_pop($stack);
            if ($dircont = scandir($thisdir)) {
                $i = 0;
                while (isset($dircont[$i])) {
                    if ($dircont[$i] !== '.' && $dircont[$i] !== '..') {
                        $current_file = "{$thisdir}" . DIRECTORY_SEPARATOR . "{$dircont[$i]}";
                        if (is_file($current_file)) {
                            $path[] = "{$thisdir}" . DIRECTORY_SEPARATOR . "{$dircont[$i]}";
                        } elseif (is_dir($current_file)) {
                            $path[] = "{$thisdir}" . DIRECTORY_SEPARATOR . "{$dircont[$i]}";
                            $stack[] = $current_file;
                        }
                    }
                    $i++;
                }
            }
        }

        // If the result needs to be organized
        if ($sortitems === true) {
            // Calls the function which is responsable for organize the files inside the path
            $dirarray = ($fileData = \Modules\InsiderFramework\Core\FileTree::fillArrayWithFileNodes(
                new \DirectoryIterator($dir)
            )
            );

            // Returning the organized result
            return $dirarray;
        }

        // Returning the result without arranging the array
        return $path;
    }
}
