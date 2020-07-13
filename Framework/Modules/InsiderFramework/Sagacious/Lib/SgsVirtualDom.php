<?php

namespace Modules\InsiderFramework\Sagacious\Lib;

use Modules\InsiderFramework\Core\KernelSpace;

/**
 * Class responsible for the SgsVirtualDom object.
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsVirtualDom
 */
class SgsVirtualDom
{
    /**
    * Recover VirtualDom object
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsController
    *
    * @return SgsVirtualDom VirtualDom object
    */
    public static function getVirtualDom(): SgsVirtualDom
    {
        $virtualDom = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'virtualDom',
            'sagacious'
        );

        return $virtualDom;
    }

    /**
    * Method description
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsVirtualDom
    *
    * @param any $data Data to be send to frontend
    *
    * @return void
    */
    public function send($data): void
    {
        echo '<vdomdata>' . json_encode($data) . '</vdomdata>';
        ob_flush();
        \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();
    }
}
