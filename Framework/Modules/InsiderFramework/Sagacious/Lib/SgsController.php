<?php

namespace Modules\InsiderFramework\Sagacious\Lib;

use Modules\InsiderFramework\Core\KernelSpace;
use Modules\InsiderFramework\Sagacious\Lib\SgsTemplate;
use Modules\InsiderFramework\Sagacious\Lib\SgsView;
use Modules\InsiderFramework\Core\Json;
use Modules\InsiderFramework\Core\FileTree;
use Modules\InsiderFramework\Core\Request;
use Modules\InsiderFramework\Sagacious\Lib\RenderEngine;

/**
 * Class with methods to be inject in Controller class of framework
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsController
 */
class SgsController
{
    public static function loadControllerClass()
    {
        $methods = get_class_methods(
            'Modules\\InsiderFramework\\Sagacious\\Lib\\SgsController'
        );

        return new class ('\Modules\InsiderFramework\Sagacious\Lib\RenderEngine') {
            use \Modules\InsiderFramework\Sagacious\Lib\Traits\SgsController;
            use \Modules\InsiderFramework\Core\Manipulation\Request;
            use \Modules\InsiderFramework\Core\Manipulation\Response;
        };
    }
}
