<?php

namespace Modules\InsiderFramework\Console;

/**
 * Main function of console
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Console\Application
 */
class Application
{
    /**
    * Manage command line request
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Application
    *
    * @param object $climate Climate object
    *
    * @return void
    */
    public static function manageCommand(&$climate): void
    {
        if ($climate->arguments->defined('help')) {
            $climate->usage();
            die();
        }

        // If action has not been defined
        if (!($climate->arguments->defined('action'))) {
            $climate->br();
            $climate->to('error')->red("Syntax error")->br();
            $climate->to('error')->write("Type <light_blue>console.php --help</light_blue> for help")->br();
                    
            $climate->usage();
            die();
        }
        
        // If action has been defined, get the action
        $climate->arguments->parse();
        $action = $climate->arguments->get('action');

        switch (strtolower($action)) {
            case 'update':
            case 'install':
                \Modules\InsiderFramework\Console\Application::installOrUpdate($climate);
                break;
            case 'remove':
            case 'delete':
                \Modules\InsiderFramework\Console\Application::remove($climate);
                break;

            case 'test':
            case 'create':
                $climate->to('error')->red("----------------------------------------");
                $climate->to('error')->red("Console function $action not implemented yet!");
                $climate->to('error')->red("----------------------------------------");
                break;

            default:
                $climate->to('error')->red("----------------------------------------");
                $climate->to('error')->red('Action ' . $action . " not recognized");
                $climate->to('error')->red("----------------------------------------");
                die();
                break;
        }
    }

    /**
    * Install or Update a module
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Application
    *
    * @param object $climate Climate object
    *
    * @return void
    */
    protected static function installOrUpdate(&$climate){
        $remote = $climate->arguments->get('remote');
        $file = $climate->arguments->get('file');

        if (
            ($remote === "" || $remote === null) &&
            ($file === "" || $file === null)
           ){
            $climate->br();
            $climate->to('error')->red("Syntax error: target (remote or file) must be specified")->br();
            $climate->to('error')->write("Type <light_blue>console.php --help</light_blue> for help")->br();
            die();
        }
        
        // Controller of modules and local authorization
        $pkgController = \Modules\InsiderFramework\Core\Loaders\CmLoader::controller('sys::pkg');
        $authorization = \Modules\InsiderFramework\Core\Manipulation\Registry::getLocalAuthorization(REQUESTED_URL);

        // Downloading the file from mirror
        if ($remote."" === ""){
            $target = $remote;
            $completePkgPath = \Modules\InsiderFramework\Core\FileTree::getAbsolutePath($target);
        } else {
            $target = $file;
            $completePkgPath = $pkgController->downloadModule($target);
        }

        switch ($completePkgPath) {
            // If can't get the file
            case "false":
                $climate->br();
                $climate->to('error')->red("Module cannot be found or downloaded!")->br();
                die();
            break;
            // If local module is on the latest version
            case "up-to-date":
                $climate->br();
                $climate->to('error')->blue("Module already up-to-date")->br();
                die();
            break;
        }
        
        // Checking if module is already storage on local file system
        if (!file_exists($completePkgPath) || !is_readable($completePkgPath)) {
            $climate->br();
            $climate->to('error')->red("File not found or not readable: " . $completePkgPath)->br();
            die();
        }

        // Checking extension of module
        $extension = strtolower(pathinfo($completePkgPath)['extension']);
        if ($extension !== "pkg") {
            $climate->br();
            $climate->to('error')->red("The file not seems to be a valid module: " . $completePkgPath)->br();
            die();
        }

        // Trying to extract the pkg file to temporary directory
        $tmpDir = INSTALL_DIR . DIRECTORY_SEPARATOR . "framework" . DIRECTORY_SEPARATOR .
                  "cache" . DIRECTORY_SEPARATOR . "tmpUpdateDir_" . uniqid();

        while (is_dir($tmpDir) || is_file($tmpDir)) {
            $tmpDir = INSTALL_DIR . DIRECTORY_SEPARATOR . "framework" . DIRECTORY_SEPARATOR . "cache" .
            DIRECTORY_SEPARATOR . "tmpUpdateDir_" . uniqid();
        }

        // Creating the temporary directory
        \Modules\InsiderFramework\Core\FileTree::createDirectory($tmpDir, 777);

        // Extracting the file
        try {
            $phar = new PharData($completePkgPath);
            $phar->extractTo($tmpDir, null, true);
        } catch (Exception $e) {
            $climate->br();
            $climate->to('error')->red("The file seems to be corrupted: " . $completePkgPath)->br();
            die();
        }

        // Verifying if the module version is later than the installed version
        $controlFile = $tmpDir . DIRECTORY_SEPARATOR . "registry" . DIRECTORY_SEPARATOR . "control.json";
        if (!file_exists($controlFile) || !is_readable($controlFile)) {
            \Modules\InsiderFramework\Console\Application::stopInstallUpdate($tmpDir, "File not found or not readable: " . $controlFile);
        }

        // Trying to read the JSON file
        $jsonData = \Modules\InsiderFramework\Core\Json::getJSONDataFile($controlFile);
        if ($jsonData === false) {
            \Modules\InsiderFramework\Console\Application::stopInstallUpdate($tmpDir, "Cannot read control file: " . $controlFile);
        }

        $missingInfoError = [];
        if (!isset($jsonData['module']) || trim($jsonData['module']) === "") {
            $missingInfoError[] = "Information missing at control file: module";
        }
        if (!isset($jsonData['version']) || trim($jsonData['version']) === "") {
            $missingInfoError[] = "Information missing at control file: version";
        }
        if ((!isset($jsonData['installed-size']) || trim($jsonData['installed-size']) === "")) {
            $missingInfoError[] = "Information missing at control file: installed-size";
        }
        if (!isset($jsonData['maintainer']) || trim($jsonData['maintainer']) === "") {
            $missingInfoError[] = "Information missing at control file: maintainer";
        }
        if (!isset($jsonData['provides'])) {
            $missingInfoError[] = "Information missing at control file: provides";
        }
        if (!isset($jsonData['depends'])) {
            $missingInfoError[] = "Information missing at control file: depends";
        }
        if (!isset($jsonData['recommends'])) {
            $missingInfoError[] = "Information missing at control file: recommends";
        }
        if (!isset($jsonData['postinst-cmd'])) {
            $missingInfoError[] = "Information missing at control file: postinst-cmd";
        }
        if (!isset($jsonData['postrm-cmd'])) {
            $missingInfoError[] = "Information missing at control file: postrm-cmd";
        }
        if (!isset($jsonData['preinst-cmd'])) {
            $missingInfoError[] = "Information missing at control file: preinst-cmd";
        }
        if (!isset($jsonData['prerm-cmd'])) {
            $missingInfoError[] = "Information missing at control file: prerm-cmd";
        }
        if (!isset($jsonData['description']) || trim($jsonData['description']) === "") {
            $missingInfoError[] = "Information missing at control file: description";
        }
        
        if (count($missingInfoError) > 0) {
            \Modules\InsiderFramework\Console\Application::stopInstallUpdate($tmpDir, implode("|", $missingInfoError));
        }

        $newModule = $jsonData['module'];
        $newModuleVersion = $jsonData['version'];
        $newModuleSection = $jsonData['section'];

        // Checking if module is installed
        $installedItemInfo = json_decode($pkgController->getInstalledItemInfo(
            $newModule,
            $authorization,
            false
        ), true);

        // If is installed, compare the current version with candidate version
        if ($installedItemInfo !== null) {
            $installedVersionParts = \Modules\InsiderFramework\Core\Manipulation\Registry::getVersionParts(
                $installedItemInfo['version']
            );
            if ($installedVersionParts === false) {
                \Modules\InsiderFramework\Console\Application::stopInstallUpdate(
                    $tmpDir,
                    "Wrong version of installed module " . $newModule . ": " . $installedItemInfo['version']
                );
            }

            $newModuleVersionParts = \Modules\InsiderFramework\Core\Manipulation\Registry::getVersionParts($newModuleVersion);
            if ($newModuleVersionParts === false) {
                \Modules\InsiderFramework\Console\Application::stopInstallUpdate("Wrong version of new module " . $newModule . ": " . $newModuleVersion);
            }

            $installedVersionString = $pkgController->getVersionFromInfo($installedVersionParts);
            $newModuleVersionString = $pkgController->getVersionFromInfo($newModuleVersionParts);

            $state = version_compare($installedVersionString, $newModuleVersionString);
            switch (true) {
                case $state < 0:
                    break;
                case $state === 0:
                    $climate->br();
                    $input = $climate
                             ->to('out')
                             ->input('Installed module version equal to the new module. ' .
                             'Do you want to overwrite (y / N)?')
                             ->accept(['s', 'S', 'y', 'Y', 'n', 'N']);
                    $response = $input->prompt();

                    switch (strtolower($response)) {
                        case 'y':
                        case 's':
                            break;

                        case 'n':
                            \Modules\InsiderFramework\Console\Application::stopInstallUpdate($tmpDir, "Aborting install");
                            break;
                    }
                    break;
                case $state > 0:
                    $climate->br();
                    $input = $climate
                             ->to('out')
                             ->input('Version of installed module greater than new module. ' .
                                     'Do you wish to continue (y/N) ?')
                            ->accept(['s', 'S', 'y', 'Y', 'n', 'N']);
                    $response = $input->prompt();

                    switch (strtolower($response)) {
                        case 'y':
                        case 's':
                            break;

                        case 'n':
                            \Modules\InsiderFramework\Console\Application::stopInstallUpdate($tmpDir, "");
                            break;
                    }
                    break;
                default:
                    \Modules\InsiderFramework\Console\Application::stopInstallUpdate(
                        $tmpDir,
                        "Error verifying module version " . $newModule . ": " . $newModuleVersion
                    );
                    break;
            }
        }
      
        // Copying registry files
        $newmoduleDirectory = INSTALL_DIR . DIRECTORY_SEPARATOR . "framework" .
                               DIRECTORY_SEPARATOR . "registry" . DIRECTORY_SEPARATOR .
                               "controls" . DIRECTORY_SEPARATOR . $newModule;

        if (!is_dir($newmoduleDirectory)) {
            Modules\InsiderFramework\Core\FileTree::createDirectory($newmoduleDirectory, 777);
        }
        Modules\InsiderFramework\Core\FileTree::copyDirectory(
            $tmpDir . DIRECTORY_SEPARATOR . "registry",
            $newmoduleDirectory
        );
        
        $directory = null;
        if (strtolower($newModuleSection) === 'component') {
            $directory = $installedItemInfo['directory'];
        }
        
        // Registering new item
        $pkgController->registerItem($newModuleSection, $newModule, $newModuleVersion, $directory);

        // Running the pre-install script
        $preInstallFile = $tmpDir . DIRECTORY_SEPARATOR . "registry" . DIRECTORY_SEPARATOR . "preinst.php";
        if (file_exists($preInstallFile)) {
            Modules\InsiderFramework\Core\FileTree::requireOnceFile($preInstallFile);
        }
        
        // Copying the files to the root
        Modules\InsiderFramework\Core\FileTree::copyDirectory($tmpDir . DIRECTORY_SEPARATOR . "data", INSTALL_DIR);

        // Running the pos-install script
        $posInstallFile = $tmpDir . DIRECTORY_SEPARATOR . "registry" . DIRECTORY_SEPARATOR . "posinst.php";
        if (file_exists($posInstallFile)) {
            Modules\InsiderFramework\Core\FileTree::requireOnceFile($posInstallFile);
        }

        // Erasing temporary directory
        Modules\InsiderFramework\Core\FileTree::deleteDirectory($tmpDir);
        
        $climate->br();
        $climate->to('error')->blue("Module installed succefully: " . basename($completePkgPath))->br();
        die();
    }

    /**
    * Remove a module
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Application
    *
    * @param object $climate Climate object
    *
    * @return void
    */
    protected static function remove(&$climate): void {
        $authorization = \Modules\InsiderFramework\Core\Manipulation\Registry::getLocalAuthorization(REQUESTED_URL);
        
        $section = $climate->arguments->get('section');
        if (!($section)) {
            $climate->br();
            $climate->to('error')->red("Syntax error: section needs to be specified for uninstall from mirrors!")->br();
            $climate->to('error')->write("Type <light_blue>console.php --help</light_blue> for help")->br();
            die();
        }

        $target = $climate->arguments->get('[additionalParameters]');
        if ($target === "") {
            $climate->br();
            $climate->to('error')->red("Syntax error: target must be specified")->br();
            $climate->to('error')->write("Type <light_blue>console.php --help</light_blue> for help")->br();
            die();
        }

        $pkgController = \Modules\InsiderFramework\Core\Loaders\CmLoader::controller('sys::pkg');
        $localVersion = $pkgController->getInstalledItemInfo($target, $authorization);
        if ($localVersion === null) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister("The $section/$target is not installed");
        } else {
            die('to do: uninstall module');
        }
    }

    /**
    * Initialize console environment
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Application
    *
    * @param object $climate Climate object
    *
    * @return void
    */
    public static function initialize(&$climate): void
    {
        \Modules\InsiderFramework\Console\Application::loadOperations($climate);
    }

    /**
    * Load possible actions and additional parameters for console operations
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Application
    *
    * @return array Array of actions and parameters
    */
    protected static function getActionsAndParameters(){
        $validActions = [];
        $validActions["install"] = array(
            "aliases" => ["update"],
            "description" => "
                              >> Install or update <<

                              # Update module (from file)
                              php console.php -a install --file insider-framework.ifm
                          
                              # Update module (from mirror)
                              php console.php -a install --remote sagacious
                              
                              # Action 'update' is an alias to install
                              php console.php -a update --remote sagacious
                              
                              # Note that how to update the framework is the same 
                              # as updating a module. This is because the 
                              # framework is considered a module by itself",
            "class" => "Modules\\InsiderFramework\Console\\Application",
            "function" => "installOrUpdate" 
        );
        
        $validActions["remove"] = array (
            "aliases" => ["uninstall"],
            "description" => "
                              >> Uninstall/remove <<

                              # Uninstall module (from mirror)
                              php console.php -a uninstall --module sagacious -s guild
            
                              # Action 'remove' is an alias to uninstall
                              php console.php -a remove --module sagacious -s guild",
            "class" => "Modules\\InsiderFramework\Console\\Application",
            "function" => "remove" 
        );

        $validActions["create"] = array (
            "aliases" => [],
            "description" => "
                              >> Create <<

                              # Create app:
                              php console.php -a create -s app --appname newStart",
            "class" => "Modules\\InsiderFramework\Console\\Application",
            "function" => "remove" 
        );

        $validActions["test"] = array (
            "aliases" => [],
            "description" => "
                              >> Tests <<

                              # Running tests:
                              php console.php -a test --class sys/basic",
            "class" => "Modules\\InsiderFramework\Console\\Application",
            "function" => "remove" 
        );

        $additionalParameters = array(
            'file' => [
                'longPrefix'   => 'file',
                'description'  => 'File path',
            ],
            'remote' => [
                'longPrefix'   => 'remote',
                'description'  => 'Remote path',
            ],
            'module' => [
                'longPrefix'   => 'module',
                'description'  => 'Module name',
            ],
            'appname' => [
                'longPrefix'   => 'appname',
                'description'  => 'App name',
            ],
            'class' => [
                'longPrefix'   => 'Class',
                'description'  => 'Class of item',
            ],
            'target' => [
                'prefix'       => 't',
                'longPrefix'   => 'target',
                'description'  => 'Target item',
            ],
            'section' => [
                'prefix'       => 's',
                'longPrefix'   => 'section',
                'description'  => 'Section of item',
            ],
            'remotepath' => [
                'prefix'       => 'rp',
                'longPrefix'   => 'remotepath',
                'description'  => 'Remote path of item on repository'
            ]
        );

        return array(
            'validActions' => $validActions,
            'additionalParameters' => $additionalParameters
        );
    }

    /**
    * Load possible operations on console
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Application
    *
    * @param object $climate Climate object
    *
    * @return void
    */
    protected static function loadOperations(&$climate): void
    {
        $actionsAndParameters = \Modules\InsiderFramework\Console\Application::getActionsAndParameters();

        $parsedActions = "";

        foreach ($actionsAndParameters['validActions'] as $validAction) {
            $parsedActions .= $validAction['description']."\n";
        }

        $helpArray = array(
            'help' => [
                'prefix'       => 'h',
                'longPrefix'   => 'help',
                'description'  => "Shows this help"
            ]
        );

        $actionsArray = array(
            'action' => [
                'prefix'       => 'a',
                'longPrefix'   => 'action',
                'description'  => "Action to be executed. Valid actions are: ".
                                $parsedActions,
                'required'    => true,
            ]
        );

        $arguments = array_merge(
            $helpArray,
            $actionsArray,
            $actionsAndParameters['additionalParameters']
        );

        $climate->arguments->add($arguments);
    }

    /**
     * Function thats process an update or install of module
     *
     * @author Marcello Costa
     *
     * @package Core
     *
     * @param string $tmpDir  Temporary directory
     * @param string $message Message of error
     *
     * @return void
    */
    protected static function stopInstallUpdate($tmpDir, $message): void
    {
        $climate = \Modules\InsiderFramework\Core\KernelSpace::getVariable('climate', 'insiderFrameworkSystem');

        // Erasing temporary directory
        Modules\InsiderFramework\Core\FileTree::deleteDirectory($tmpDir);

        // Showing the error
        $climate->br();
        $climate->to('error')->red($message)->br();

        // Stopping script
        die();
    }
}
