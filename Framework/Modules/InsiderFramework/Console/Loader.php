<?php

chdir(dirname(__FILE__, 4));

if (basename(getcwd()) !== "Framework") {
    die("\n\rWrong base path of console executable\n\r\n\r");
}

// Initializing framework
require_once(
    'Modules' .
    DIRECTORY_SEPARATOR .
    'InsiderFramework' .
    DIRECTORY_SEPARATOR .
    'Core' .
    DIRECTORY_SEPARATOR .
    'System.php'
);

\Modules\InsiderFramework\Core\System::initializeFramework();

/**
 * @global array Variable used by framework to control requests that are maked by console
 *
 * @package Core
*/
\Modules\InsiderFramework\Core\KernelSpace::setVariable(
    array(
        'consoleRequest' => "UpdateAgent"
    ),
    'insiderFrameworkSystem'
);

// Initializing Climate
$climate = new \League\CLImate\CLImate();

\Modules\InsiderFramework\Console\Application::initialize($climate);

\Modules\InsiderFramework\Core\KernelSpace::setVariable(array('climate' => $climate), 'insiderFrameworkSystem');

\Modules\InsiderFramework\Console\Application::manageCommand($climate);
