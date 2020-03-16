<?php

// Initializing Climate
$climate = new \League\CLImate\CLImate;
$kernelspace->setVariable(array('climate' => $climate), 'insiderFrameworkSystem');

// Default actions of console
$consoleDefaultActions = [
    'help' => [
        'prefix' => 'h',
        'longPrefix' => 'help',
        'description' => "Shows this help"
    ],
    'install' => [
        'prefix' => 'i',
        'longPrefix' => 'install',
        'description' => "Install/update package 
            
                           # Install/Update package (from file)
                           php console.php -i insider-framework.pkg

                           # Install/Update package (from mirror)
                           php console.php -i sagacious
                           
                           # Note that how to update the framework is the same 
                           # as updating a package. This is because the 
                           # framework is considered a package by itself
                          ",
        'depends' => []
    ],
    'uninstall' => [
        'prefix' => 'u',
        'longPrefix' => 'uninstall',
        'description' => "Uninstall package 
                            
                           # Uninstall package
                           php console.php -u sagacious -s guild
                          ",
        'depends' => [
            'section'
        ]
    ],
    'remove' => [
        'longPrefix' => 'remove',
        'description' => "Remove package
                           php console.php remove sagacious -s guild
                           
                           * Action 'remove' is an alias to uninstall
                          ",
        'depends' => []
    ],
    'delete' => [
        'longPrefix' => 'delete',
        'description' => "Delete package 
                            
                           # Remove package
                           php console.php delete sagacious -s guild
                           
                           * Action 'remove' is an alias to uninstall
                          ",
        'depends' => []
    ],
    'generate' => [
        'prefix' => 'g',
        'longPrefix' => 'generate',
        'description' => "Generate package directory tree
                            
                           php console.php -g package_name (optional)
                          ",
        'depends' => []
    ],
    'build' => [
        'prefix' => 'b',
        'longPrefix' => 'build',
        'description' => "Build an package file from an directory tree 
                            
                           php console.php -b directory
                          ",
        'depends' => []
    ],
    'run-tests' => [
        'longPrefix' => 'run-tests',
        'description' => "Run tests from an pack 
                            
                           php console.php -run-tests sys/basic
                           OR
                           php console.php -run-tests sys/basic::testController
                           OR
                           php console.php -run-tests sys/basic::testController::testMethod()
                          ",
        'depends' => []
    ],
    'create' => [
        'longPrefix' => 'create',
        'description' => "Create a pack, controller, model, view or template
                            
                           php console.php --create newPack -s pack
                           php console.php --create start/viewName -s view
                           php console.php --create start/templateName -s template
                          ",
        'depends' => [
            'section'
        ]
    ]
];

// Default optinal arguments for actions of console
$consoleOptionalArgs = [
    // Optional arguments
    'section' => [
        'prefix' => 's',
        'longPrefix' => 'section',
        'description' => 'Section of item',
    ]
];

/* Loading libs of console */
$insiderModulerDirectory = INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'insiderconsole';
\KeyClass\FileTree::requireOnceFile($insiderModulerDirectory.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'build.php');
\KeyClass\FileTree::requireOnceFile($insiderModulerDirectory.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'directoryTreeGenerator.php');
\KeyClass\FileTree::requireOnceFile($insiderModulerDirectory.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'packagemanager.php');
\KeyClass\FileTree::requireOnceFile($insiderModulerDirectory.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'validate.php');

// Loading action manager
\KeyClass\FileTree::requireOnceFile($insiderModulerDirectory.DIRECTORY_SEPARATOR.'actionManager.php');

$kernelspace->setVariable(array('consoleOptionalArgs' => $consoleOptionalArgs), 'insiderFrameworkSystem');
$kernelspace->setVariable(array('consoleDefaultActions' => $consoleDefaultActions), 'insiderFrameworkSystem');

$climate->arguments->add(array_merge($consoleDefaultActions, $consoleOptionalArgs));