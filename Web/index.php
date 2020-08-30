<?php

// Silence always comes with a price
require_once(
    '..' . DIRECTORY_SEPARATOR .
    'Framework' . DIRECTORY_SEPARATOR .
    'Modules' . DIRECTORY_SEPARATOR .
    'InsiderFramework' . DIRECTORY_SEPARATOR .
    'Core' . DIRECTORY_SEPARATOR .
    'Bootstrap.php'
);

\Modules\InsiderFramework\Core\Bootstrap::initializeFramework();
\Modules\InsiderFramework\Core\CpuAvg::checkCpuUsage();
\Modules\InsiderFramework\Core\RoutingSystem\Request::requestRoute();
