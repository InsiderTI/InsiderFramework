<?php

namespace Modules\InsiderFramework\Sagacious\Components\View;

use Modules\InsiderFramework\Sagacious\Lib\SgsPage;
use Modules\InsiderFramework\Sagacious\Lib\SgsComponent;
use Modules\InsiderFramework\Core\Validation\Aggregation;

/**
 * View (SgsComponent)
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Components\View\View
 */
class View extends SgsComponent
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

        if (
            !isset($props['params']) ||
            !Aggregation::existAndIsNotEmpty($props, 'doublecolonviewpath')
        ) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister('Invalid properties on View component');
        }

        $params = $props['params'];

        $pattern = "/" . "((?P<app>.*)::)?(?P<viewPath>.*)" . "/";
        preg_match_all($pattern, $props['doublecolonviewpath'], $viewFilenameMatches, PREG_SET_ORDER);
        if (
            !is_array($viewFilenameMatches) ||
            (
             is_array($viewFilenameMatches) &&
             count($viewFilenameMatches) === 0
             )
        ) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister('Invalid doublecolonviewpath property on View component');
        }
        $app = $viewFilenameMatches[0]['app'];
        $viewPath = $viewFilenameMatches[0]['viewPath'];

        $controller_view = new \Modules\InsiderFramework\Core\Controller($app, $params);
        $this->code = $controller_view->renderViewToString($props['doublecolonviewpath']);
    }
}
