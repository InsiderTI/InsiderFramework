<?php

// Silence always comes with a price
require_once(
    '..' . DIRECTORY_SEPARATOR .
    'Framework' . DIRECTORY_SEPARATOR .
    'Modules' . DIRECTORY_SEPARATOR .
    'InsiderFramework' . DIRECTORY_SEPARATOR .
    'Core' . DIRECTORY_SEPARATOR .
    'System.php'
);

\Modules\InsiderFramework\Core\System::initializeFramework();
\Modules\InsiderFramework\Core\System::checkCpuUsage();
\Modules\InsiderFramework\Core\RoutingSystem\Request::requestRoute();
