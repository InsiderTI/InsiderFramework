<?php

namespace Modules\InsiderFramework\Sagacious\Components\ViewsBag;

use Modules\InsiderFramework\Sagacious\Lib\SgsComponent;
use Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag;

/**
 * Main class of the viewsBag component (SgsComponent)
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Components\ViewsBag\ViewsBag
 */
class ViewsBag extends SgsComponent
{
    /**
     * Initialize code of the component
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Components\ViewsBag\ViewsBag
     *
     * @return void
     */
    public function initialize(): void
    {
        $stateData = $this->getStates()->getCurrentState();
        $props = $stateData['props'];
        $viewsBag = SgsViewsBag::get($props['field']);
        $this->code = $viewsBag;
    }
}
