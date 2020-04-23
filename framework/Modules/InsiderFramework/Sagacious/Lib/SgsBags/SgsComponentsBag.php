<?php

namespace Modules\InsiderFramework\Sagacious\Lib\SgsBags;

use Modules\InsiderFramework\Core\KernelSpace;
use Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates;
use Modules\InsiderFramework\Core\Development;

/**
 * Class of SgsComponentsBag object
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsComponentsBag
 */
class SgsComponentsBag
{
    protected $componentsArray = [];

    /**
    * Define in array the current state of a view component
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsComponentsBag
    *
    * @param array $componentData Component data from view
    *
    * @return void
    */
    public static function initializeComponentComponentsBag(array $componentData): void
    {
        $componentId = $componentData['id'];
        $componentApp = $componentData['app'];

        $states = new SgsComponentStates($componentId, $componentApp);
        $defaultState = $states->getCurrentState();

        if (!isset($defaultState['class'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                'Class not found in "' . $componentId . '" SgsComponent. Check component state configuration.'
            );
        }

        $registryData = \Modules\InsiderFramework\Core\Registry::getComponentRegistryData(
            $defaultState['class']
        );

        $SagaciousComponentsDirectory = KernelSpace::getVariable(
            'SagaciousComponentsDirectory',
            'sagacious'
        );

        $completeComponentPath = "\\Modules\\InsiderFramework\\Sagacious\\Components\\" .
        $registryData['class'];

        $componentsBagObj = KernelSpace::getVariable(
            'componentsBag',
            'sagacious'
        );

        if (is_null($componentsBagObj->get($componentId))) {
            $componentObj = new $completeComponentPath($componentId, $componentApp);
            $componentsBagObj->set(
                $componentId,
                $componentObj
            );
        }
    }

    /**
    * Get component from ComponentsBag
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsComponentsBag
    *
    * @param string $componentId Component Id
    *
    * @return SgsComponent Sagacious Component
    */
    public static function get(string $componentId)
    {
        $componentsBagObj = KernelSpace::getVariable(
            'componentsBag',
            'sagacious'
        );

        if (!isset($componentsBagObj->componentsArray[$componentId])) {
            return null;
        }

        return $componentsBagObj->componentsArray[$componentId];
    }
    
    /**
    * Register a component in ComponentsBag
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsComponentsBag
    *
    * @param string       $componentId     Component id
    * @param SgsComponent $componentObject Component object
    *
    * @return void
    */
    public static function set(
        string $componentId,
        $componentObject
    ): void {
        Development::validateParameter(
            1,
            $componentObject,
            'Modules\InsiderFramework\Sagacious\Lib\SgsComponent'
        );

        $componentsBagObj = KernelSpace::getVariable(
            'componentsBag',
            'sagacious'
        );

        $componentsBagObj->componentsArray[$componentId] = $componentObject;

        KernelSpace::setVariable(
            array(
            'componentsBag' => $componentsBagObj
            ),
            'sagacious'
        );
    }

    /**
     * Erases some component of the componentssBag by the key
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsComponentsBag
     *
     * @param string $componentId ComponentId that will be erased
     *
     * @return void
     */
    public static function remove($componentId): void
    {
        $componentsBagObj = KernelSpace::getVariable(
            'componentsBag',
            'sagacious'
        );

        if (isset($componentsBagObj->componentsArray[$componentId])) {
            unset($componentsBagObj->componentsArray[$componentId]);
        }

        KernelSpace::setVariable(
            array(
                'componentsBag' => $componentsBagObj
            ),
            'sagacious'
        );
    }

    /**
     * Erases all content of the componentsBag
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsComponentsBag
     *
     * @return void
     */
    public static function removeAll(): void
    {
        $componentsBagObj = KernelSpace::getVariable(
            'componentsBag',
            'sagacious'
        );

        $componentsBagObj->componentsArray = [];

        KernelSpace::setVariable(
            array(
                'componentsBag' => $componentsBagObj
            ),
            'sagacious'
        );
    }
}
