<?php

/**
 * File that loads sagacious settings and classes
 */

require_once 'ConfigSagacious.php';

\Modules\InsiderFramework\Core\KernelSpace::setVariable(
    array(
        'componentsBag' => new \Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsComponentsBag()
    ),
    'sagacious'
);

\Modules\InsiderFramework\Core\KernelSpace::setVariable(
    array(
        'viewsBag' => new \Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag()
    ),
    'sagacious'
);

\Modules\InsiderFramework\Core\KernelSpace::setVariable(
    array(
        'virtualDom' => new \Modules\InsiderFramework\Sagacious\Lib\SgsVirtualDom()
    ),
    'sagacious'
);

$class = \Modules\InsiderFramework\Sagacious\Lib\SgsController::loadControllerClass();

$anonymousClass = get_class($class);
class_alias($anonymousClass, '\Modules\InsiderFramework\Core\RenderEngine');
