<?php

namespace Modules\Insiderframework\Core\FileTree;

/**
 * Methods responsible for handle files and directories
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\FileTree\FileTreeTrait
 *
 */
trait FileTreeTrait
{
    /**
     * Gets the content of an file
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\FileTree\FileTreeTrait
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
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError('Variable delaytry is not numeric');
        }

        if ($maxToleranceLoops === null) {
            if (!defined("MAX_TOLERANCE_LOOPS")) {
                $maxToleranceLoops = 1000;
            } else {
                $maxToleranceLoops = MAX_TOLERANCE_LOOPS;
            }
        }

        if ($filepath === null) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The file path not specified in \Modules\InsiderFramework\Core\FileTree\FileTreeTrait::fileReadContent()"
            );
        }

        $countToleranceLoops = 0;
        $idError = null;

        while (file_exists($filepath . ".lock")) {
            $maxToleranceLoops++;
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
                $countToleranceLoops = 0;

                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
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
}
