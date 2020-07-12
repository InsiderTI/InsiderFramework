<?php

namespace Modules\InsiderFramework\Sagacious\Components\CodeInjected;

use Modules\InsiderFramework\Sagacious\Lib\SgsComponent;
use Modules\InsiderFramework\Core\KernelSpace;

/**
 * Main class of the Codeinjected component (SgsComponent)
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Components\Codeinjected
 */
class Codeinjected extends SgsComponent
{
    /**
     * Initialize code of the component
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Components\View\View
     *
     * @return void
     */
    public function initialize(): void
    {
        $stateData = $this->getStates()->getCurrentState();
        $props = $stateData['props'];

        if (isset($props['code'])) {
            $this->code = $props['code'];
        } elseif (isset($props['injectedVariable'])) {
            $scope = 'sagacious';
            if (isset($props['injectedVariableScope'])) {
                $scope = $props['injectedVariableScope'];
            }
            $this->code = KernelSpace::getVariable(
                $props['injectedVariable'],
                $scope
            );
        }
    }
}
