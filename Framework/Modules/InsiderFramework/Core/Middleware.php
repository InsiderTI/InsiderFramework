<?php

namespace Modules\InsiderFramework\Core;

use Modules\InsiderFramework\Core\KernelSpace;
use Modules\InsiderFramework\Core\Aggregation;

/**
 * Class responsible for handle middlewares
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Mail
 */
class Middleware
{
    /**
    * Method description
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Mail
    *
    * @param string $identity           Identity of middleware
    *
    * @return array Middleware data
    */
    public static function get(string $identity): array
    {
        $middlewares = KernelSpace::getVariable(
            'middlewares',
            'insiderFrameworkSystem'
        );

        if (
            is_array($middlewares) &&
            isset($middlewares[strtoupper($identity)])
        ) {
            return $middlewares[strtoupper($identity)]['data'];
        }

        return [];
    }

    /**
    * Register middleware for a class
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Mail
    *
    * @param string $identifier         Identifier of middleware
    * @param string $fullclassName      Full class name to be called
    * @param string $functionName       Middleware function
    * @param array  $arguments          Arguments of middleware function
    *
    * @return void
    */
    public static function set(
        string $identity,
        string $fullClassName,
        string $functionName,
        array $arguments = []
    ): void {
        KernelSpace::setVariable(
            array(
                'middlewares' => array(
                    strtoupper($identity) => array(
                        'data' => array(
                            'fullclassname' => $fullClassName,
                            'function' => $functionName,
                            'arguments' => $arguments
                        )
                    )
                )
            ),
            'insiderFrameworkSystem'
        );
    }

    /**
    * Load middleware
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Mail
    *
    * @param string $identifier         Identifier of middleware
    * @param array  $arguments          Arguments of middleware function
    *
    * @return mixed Return of middleware
    */
    public static function call(string $identifier, array &$arguments = [])
    {
        global $kernelspace;

        $middleware = \Modules\InsiderFramework\Core\Middleware::get(
            strtoupper($identifier)
        );

        if (
            is_null($middleware) ||
            (is_array($middleware) && empty($middleware))
        ) {
            return;
        }

        $className = $middleware['fullclassname'];
        $functionName = $middleware['function'];

        return call_user_func("$className::$functionName", $arguments);
    }
}
