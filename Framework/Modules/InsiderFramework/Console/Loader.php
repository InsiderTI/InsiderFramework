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
    'Bootstrap.php'
);

\Modules\InsiderFramework\Core\Bootstrap::initializeFramework();

// Initializing Console
$console = \Modules\InsiderFramework\Console\Application::createConsoleInstance();

\Modules\InsiderFramework\Console\Application::initialize($console);

\Modules\InsiderFramework\Core\KernelSpace::setVariable(
    array(
        'console' => $console
    ),
    'insiderFrameworkSystem'
);

\Modules\InsiderFramework\Console\Application::manageCommand($console);
