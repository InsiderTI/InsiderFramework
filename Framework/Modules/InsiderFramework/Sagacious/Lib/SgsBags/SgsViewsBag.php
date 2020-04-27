<?php

namespace Modules\InsiderFramework\Sagacious\Lib\SgsBags;

/**
 * Class of SgsviewsBag object
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag
 */
class SgsViewsBag
{
    protected $viewsBagArray = [];

    /**
     * Recover value from viewsBag
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag
     *
     * @param string $key Item to be recovered
     *
     * @return any Recovered value
     */
    public static function get(string $key)
    {
        $viewsBagObj = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'viewsBag',
            'sagacious'
        );

        if (isset($viewsBagObj->viewsBagArray[$key])) {
            return $viewsBagObj->viewsBagArray[$key];
        }
    }

    /**
     * Set value to viewsBag
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag
     *
     * @param string $key   Item to be set
     * @param any    $value Value to be set
     *
     * @return void
     */
    public static function set(string $key, $value): void
    {
        $viewsBagObj = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'viewsBag',
            'sagacious'
        );

        $viewsBagObj->viewsBagArray[$key] = $value;

        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'viewsBag' => $viewsBagObj
            ),
            'sagacious'
        );
    }

    /**
     * Erases some content of the viewsBag by the key
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag
     *
     * @param string $key Key of value that will be erased
     *
     * @return void
     */
    public static function remove($key): void
    {
        $viewsBagObj = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'viewsBag',
            'sagacious'
        );

        if (isset($viewsBagObj->viewsBagArray[$key])) {
            unset($viewsBagObj->viewsBagArray[$key]);
        }

        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'viewsBag' => $viewsBagObj
            ),
            'sagacious'
        );
    }

    /**
     * Erases all content of the viewsBag
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag
     *
     * @return void
     */
    public static function removeAll(): void
    {
        $viewsBagObj = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'viewsBag',
            'sagacious'
        );

        $viewsBagObj->viewsBagArray = [];

        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'viewsBag' => $viewsBagObj
            ),
            'sagacious'
        );
    }
}
