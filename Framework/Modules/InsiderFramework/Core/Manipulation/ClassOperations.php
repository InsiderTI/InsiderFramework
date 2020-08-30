<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods responsible for handle class operations
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Manipulation\ClassOperations
 */
trait ClassOperations
{
    /**
    * Get the app name by class name
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Manipulation\ClassOperations
    *
    * @param string $completeClassName Full class name
    *
    * @return string App name
    */
    public static function getAppNameByClassName($completeClassName): string
    {
        $appName = explode("\\", $completeClassName)[1];
        return $appName;
    }

    /**
    * Get the classname by file path
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Manipulation\ClassOperations
    *
    * @param string $controllerFilePath Controller file path
    *
    * @return string Full class name
    */
    public static function getClassNameByFilePath(string $controllerFilePath): string
    {
        $completeClassName = str_replace("/", "\\", substr($controllerFilePath, 0, -4));
        return $completeClassName;
    }

    /**
    * Get a reflection controller object by file path
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Manipulation\ClassOperations
    *
    * @param string $controllerFilePath Controller file path
    *
    * @return object Controller reflection object
    */
    public static function getReflectionControllerObjectByFilePath(string $controllerFilePath)
    {
        $completeClassName = ClassOperations::getClassNameByFilePath($controllerFilePath);
        $reflectionControllerObj = new \ReflectionClass($completeClassName);

        return $reflectionControllerObj;
    }
}
