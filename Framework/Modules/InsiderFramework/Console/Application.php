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

        $actionsAndParameters = \Modules\InsiderFramework\Console\Application::getActionsAndParameters();

        if (!isset($actionsAndParameters['validActions'][strtolower($action)])){
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister("Action '$action' not found");
        }

        $function = $actionsAndParameters['validActions'][strtolower($action)]['function'];
        $class = $actionsAndParameters['validActions'][strtolower($action)]['class'];

        call_user_func("$class::$function", $climate);
    }

    /**
    * Install or Update a package
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
        
        $pkgController = new \Apps\Sys\PkgController();
        $authorization = \Modules\InsiderFramework\Core\Registry::getLocalAuthorization(REQUESTED_URL);

        if ($remote."" !== ""){
            $target = $remote;
            $completePkgPath = $pkgController->downloadPackage($target);
        } else {
            $target = $file;
            $completePkgPath = \Modules\InsiderFramework\Core\FileTree::getAbsolutePath($target);
        }

        switch ($completePkgPath) {
            // If can't get the file
            case "false":
                $climate->br();
                $climate->to('error')->red("Package cannot be found or downloaded!")->br();
                die();
            break;
            // If local package is on the latest version
            case "up-to-date":
                $climate->br();
                $climate->to('error')->blue("Package already up-to-date")->br();
                die();
            break;
        }
        
        // Checking if package is already storage on local file system
        if (!file_exists($completePkgPath) || !is_readable($completePkgPath)) {
            $climate->br();
            $climate->to('error')->red("File not found or not readable: " . $completePkgPath)->br();
            die();
        }

        // Checking extension of package
        $extension = strtolower(pathinfo($completePkgPath)['extension']);
        if ($extension !== "pkg") {
            $climate->br();
            $climate->to('error')->red("The file not seems to be a valid package: " . $completePkgPath)->br();
            die();
        }

        // Trying to extract the pkg file to temporary directory
        $tmpDir = INSTALL_DIR . DIRECTORY_SEPARATOR .
                  "Framework" . DIRECTORY_SEPARATOR .
                  "Cache" . DIRECTORY_SEPARATOR .
                  "tmpUpdateDir_" .
                  uniqid();

        while (is_dir($tmpDir) || is_file($tmpDir)) {
            $tmpDir = INSTALL_DIR . DIRECTORY_SEPARATOR .
                      "Framework" . DIRECTORY_SEPARATOR .
                      "Cache" . DIRECTORY_SEPARATOR .
                      "tmpUpdateDir_" . uniqid();
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

        // Verifying if the package version is later than the installed version
        $controlFile = $tmpDir . DIRECTORY_SEPARATOR .
                       "Registry" . DIRECTORY_SEPARATOR .
                       "control.json";

        if (!file_exists($controlFile) || !is_readable($controlFile)) {
            \Modules\InsiderFramework\Console\Application::stopInstallUpdate($tmpDir, "File not found or not readable: " . $controlFile);
        }

        // Trying to read the JSON file
        $jsonData = \Modules\InsiderFramework\Core\Json::getJSONDataFile($controlFile);
        if ($jsonData === false) {
            \Modules\InsiderFramework\Console\Application::stopInstallUpdate($tmpDir, "Cannot read control file: " . $controlFile);
        }

        $missingInfoError = [];
        if (!isset($jsonData['package']) || trim($jsonData['package']) === "") {
            $missingInfoError[] = "Information missing at control file: package";
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

        $newPackage = $jsonData['package'];
        $newPackageVersion = $jsonData['version'];
        $newPackageSection = $jsonData['section'];

        // Checking if package is installed
        $installedItemInfo = json_decode($pkgController->getInstalledItemInfo(
            $newPackage,
            $authorization,
            false
        ), true);

        // If is installed, compare the current version with candidate version
        if ($installedItemInfo !== null) {
            $installedVersionParts = \Modules\InsiderFramework\Core\Registry::getVersionParts(
                $installedItemInfo['version']
            );
            if ($installedVersionParts === false) {
                \Modules\InsiderFramework\Console\Application::stopInstallUpdate(
                    $tmpDir,
                    "Wrong version of installed package " . $newPackage . ": " . $installedItemInfo['version']
                );
            }

            $newPackageVersionParts = \Modules\InsiderFramework\Core\Registry::getVersionParts($newPackageVersion);
            if ($newPackageVersionParts === false) {
                \Modules\InsiderFramework\Console\Application::stopInstallUpdate("Wrong version of new package " . $newPackage . ": " . $newPackageVersion);
            }

            $installedVersionString = $pkgController->getVersionFromInfo($installedVersionParts);
            $newPackageVersionString = $pkgController->getVersionFromInfo($newPackageVersionParts);

            $state = version_compare($installedVersionString, $newPackageVersionString);
            switch (true) {
                case $state < 0:
                    break;
                case $state === 0:
                    $climate->br();
                    $input = $climate
                             ->to('out')
                             ->input('Installed package version equal to the new package. ' .
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
                             ->input('Version of installed package greater than new package. ' .
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
                        "Error verifying package version " . $newPackage . ": " . $newPackageVersion
                    );
                    break;
            }
        }
      
        // Copying registry files
        $newpackageDirectory = INSTALL_DIR . DIRECTORY_SEPARATOR .
                               "Framework" . DIRECTORY_SEPARATOR .
                               "Registry" . DIRECTORY_SEPARATOR .
                               "Controls" . DIRECTORY_SEPARATOR .
                               $newPackage;

        if (!is_dir($newpackageDirectory)) {
            Modules\InsiderFramework\Core\FileTree::createDirectory($newpackageDirectory, 777);
        }
        Modules\InsiderFramework\Core\FileTree::copyDirectory(
            $tmpDir . DIRECTORY_SEPARATOR .
            "Registry",
            $newpackageDirectory
        );
        
        $directory = null;
        if (strtolower($newPackageSection) === 'component') {
            $directory = $installedItemInfo['directory'];
        }
        
        // Registering new item
        \Modules\InsiderFramework\Core\Registry::registerItem($newPackageSection, $newPackage, $newPackageVersion, $directory);

        // Running the pre-install script
        $preInstallFile = $tmpDir . DIRECTORY_SEPARATOR .
                          "Registry" . DIRECTORY_SEPARATOR .
                          "preinst.php";

        if (file_exists($preInstallFile)) {
            Modules\InsiderFramework\Core\FileTree::requireOnceFile($preInstallFile);
        }
        
        // Copying the files to the root
        Modules\InsiderFramework\Core\FileTree::copyDirectory(
            $tmpDir . DIRECTORY_SEPARATOR .
            "Data",
            INSTALL_DIR
        );

        // Running the pos-install script
        $posInstallFile = $tmpDir . DIRECTORY_SEPARATOR .
                          "Registry" . DIRECTORY_SEPARATOR .
                          "posinst.php";

        if (file_exists($posInstallFile)) {
            Modules\InsiderFramework\Core\FileTree::requireOnceFile($posInstallFile);
        }

        // Erasing temporary directory
        Modules\InsiderFramework\Core\FileTree::deleteDirectory($tmpDir);
        
        $climate->br();
        $climate->to('error')->blue("Package installed succefully: " . basename($completePkgPath))->br();
        die();
    }

    /**
    * Remove a package
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
        $authorization = \Modules\InsiderFramework\Core\Registry::getLocalAuthorization(REQUESTED_URL);
        
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

        $pkgController = new \Apps\Sys\PkgController();
        $localVersion = $pkgController->getInstalledItemInfo($target, $authorization);
        if ($localVersion === null) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister("The $section/$target is not installed");
        } else {
            die('to do: uninstall package');
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
        $consoleRegistryDir = "Modules" . DIRECTORY_SEPARATOR .
                              "InsiderFramework" . DIRECTORY_SEPARATOR .
                              "Console";

        $dataActions = \Modules\InsiderFramework\Core\Registry::getLocalConfigurationFile(
            $consoleRegistryDir . DIRECTORY_SEPARATOR .
            "actions.json"
        );

        $dataAdditionalParameters = \Modules\InsiderFramework\Core\Registry::getLocalConfigurationFile(
            $consoleRegistryDir . DIRECTORY_SEPARATOR .
            "additionalParameters.json"
        );

        if (
            !isset($dataActions['actions']) ||
            !isset($dataAdditionalParameters['additionalParameters'])
        ){
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister('Invalid actions/additionalParameters file of console');
        }

        foreach($dataActions['actions'] as $actionKey => $actionValue){
            if (
                !isset($dataActions['actions'][$actionKey]['description'])
            ){
                \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister('Invalid actions file of console');
            }

            $description = "\n\t\t" . implode("\n\t\t", $dataActions['actions'][$actionKey]['description']);
            $dataActions['actions'][$actionKey]['description']=$description;
        }

        return array(
            'validActions' => $dataActions['actions'],
            'additionalParameters' => $dataAdditionalParameters['additionalParameters']
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
                'description'  => "Action to be executed. Valid actions are: \n".
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
     * Function thats process an update or install of package
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
