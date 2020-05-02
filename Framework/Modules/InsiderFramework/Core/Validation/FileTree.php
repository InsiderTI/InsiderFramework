<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Methods responsible for validate for files and directories
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Validation\FileTree
 *
 */
trait FileTree
{
    /**
    * Check if directory is empty
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Validation\FileTree
    *
    * @param string $dir Directory path
    *
    * @return bool If directory is empty or not
    */
    public static function isDirEmpty($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        $filetree = \Modules\InsiderFramework\Core\FileTree::dirTree($dir);
        $fileExists = false;
        foreach ($filetree as $item) {
            if (is_file($item)) {
                $fileExists = true;
            }
        }
        
        return $fileExists;
    }
}
