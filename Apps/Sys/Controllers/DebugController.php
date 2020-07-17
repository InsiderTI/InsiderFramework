<?php

namespace Apps\Sys\Controllers;

use Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag;
use Modules\InsiderFramework\Sagacious\Lib\SgsVirtualDom;

/**
 * Class responsible for rendering debug bar
 *
 * @author Marcello Costa
 *
 * @package Apps\Sys\Controllers\DebugController
 */
class DebugController extends \Modules\InsiderFramework\Core\Controller
{
    /**
     * Render the debug bar
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\DebugController
     *
     * @return string HTML code of debug bar
    */
    public function debugBarRender(): string
    {
        $debugBarData = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'debugbar',
            'insiderFrameworkSystem'
        );

        SgsViewsBag::set('elapsedTime', $debugBarData['elapsedTime']);
        SgsViewsBag::set('memoryUsage', $debugBarData['memoryUsage']);

        $debugBarHtml = $this->renderViewToString('Sys::debugBar/index.sgv');
        return $debugBarHtml;
    }

    /**
    * Send a warning to the frontend
    *
    * @author Marcello Costa
    *
    * @package Apps\Sys\Controllers\DebugController
    *
    * @return void
    */
    public function flushWarning()
    {
        /*
        $warnings = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'warnings',
            'insiderFrameworkSystem'
        );

        $virtualDom = SgsVirtualDom::getVirtualDom();
        foreach ($warnings as $warning) {
            $warningJson = \Modules\InsiderFramework\Core\Json::jsonEncodePrivateObject($warning);
            $virtualDom->send("registerWarning('" . base64_encode($warningJson) . "');");
        }
        */
    }
}
