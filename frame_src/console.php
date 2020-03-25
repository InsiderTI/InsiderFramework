<?php
/**
    This file can be executed on terminal. This is the main file of console in
    framework. Exists in this file functions to manage packages, create
    packs, controlers, etc.
 
    @author Marcello Costa
    @package Core
*/

list($tmpBasePath) = get_included_files();
$basePath = dirname($tmpBasePath);
unset($tmpBasePath);
chdir($basePath);

// Initializing framework
require_once($basePath.DIRECTORY_SEPARATOR.'init.php');

/** 
  @global array Variable used by framework to control requests that are maked by console

  @package Core
*/
$kernelspace->setVariable(array('consoleRequest' => "UpdateAgent"), 'insiderFrameworkSystem');

// Initializing Climate
$climate = new \League\CLImate\CLImate;
$kernelspace->setVariable(array('climate' => $climate), 'insiderFrameworkSystem');
$climate->arguments->add([
    'help' => [
        'prefix'       => 'h',
        'longPrefix'   => 'help',
        'description'  => "Shows this help"
    ],
    'action' => [
        'prefix'       => 'a',
        'longPrefix'   => 'action',
        'description'  => "Action to be executed. Valid actions are: create, ".
                          "remove, install/update, uninstall/remove, test. With this you can:
                              
                            - Create/Remove packs, controllers, model's, templates, views
                            - Install/Update/Uninstall packages of framework (or the framework itself)
                            - Run tests
                            
                           Examples:
                               # ---------------------------------------------------------------
                               # Install or update:
                               # ---------------------------------------------------------------
                               # Update package (from file)
                               php console.php -a install insider-framework.pkg
                            
                               # Update package (from mirror)
                               php console.php -a install sagacious
                               
                               # Action 'update' is an alias to install
                               php console.php -a update sagacious
                               
                               # ---------------------------------------------------------------
                               # Uninstall/remove:
                               # ---------------------------------------------------------------
                               # Uninstall package (from mirror)
                               php console.php -a uninstall sagacious -s guild
                               
                               # Action 'remove' is an alias to uninstall
                               php console.php -a remove sagacious -s guild

                               # ---------------------------------------------------------------
                               # Create or remove packs, templates, views, controllers, model's
                               # ---------------------------------------------------------------
                               php console.php -a uninstall customComponent
                               php console.php -a create -s pack newStart
                               php console.php -a remove -s controller start/main
                               php console.php -a remove -s model start/example
                               php console.php -a create -s view start/example2
                               php console.php -a create -s template start/newTemplate

                               # Running tests
                               php console.php -a test sys/basic
                            
                               # Note that how to update the framework is the same 
                               # as updating a package. This is because the 
                               # framework is considered a package by itself",
        'required'    => true,
    ],
    'section' => [
        'prefix'       => 's',
        'longPrefix'   => 'section',
        'description'  => 'Section of item',
        // 'required'    => true
    ],
    'remotepath' => [
        'prefix'       => 'rp',
        'longPrefix'   => 'remotepath',
        'description'  => 'Remote path of item on repository',
        // 'required'    => true
    ],
    '[additionalParameters]' => [
        'description' => 'Additional parameters/data',
        // 'required'    => true
    ]
]);

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

/**
    Function thats process an update or install of package
    
    @author Marcello Costa
    
    @package Core
 
    @param  string  $tmpDir    Temporary directory
    @param  string  $message   Message of error
  
    @return  void  Without return
*/
function stopInstallUpdate($tmpDir, $message) : void {
    global $kernelspace;
    $climate = $kernelspace->getVariable('climate', 'insiderFrameworkSystem');

    // Erasing temporary directory
    KeyClass\Filetree::deleteDirectory($tmpDir);

    // Showing the error
    $climate->br();
    $climate->to('error')->red($message)->br();

    // Stopping script
    die();
}

switch (strtolower($action)) {
    case 'update':
    case 'install':
        $target = $climate->arguments->get('[additionalParameters]');

        if ($target === "" || $target === NULL) {
            $climate->br();
            $climate->to('error')->red("Syntax error: target must be specified")->br();
            $climate->to('error')->write("Type <light_blue>console.php --help</light_blue> for help")->br();
            die();
        }
        
        // Controller of packages and local authorization
        $pkgController = \KeyClass\Request::Controller('sys::pkg');
        $authorization = \KeyClass\Registry::getLocalAuthorization(REQUESTED_URL);

        // Downloading the file from mirror
        if (strpos($target, ".") === false){
            $completePkgPath = $pkgController->downloadPackage($target);
        }
        else{
            $completePkgPath = \KeyClass\FileTree::getAbsolutePath($target);
        }

        switch($completePkgPath){
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
            $climate->to('error')->red("File not found or not readable: ".$completePkgPath)->br();
            die();
        }

        // Checking extension of package
        $extension = strtolower(pathinfo($completePkgPath)['extension']);
        if ($extension !== "pkg") {
            $climate->br();
            $climate->to('error')->red("The file not seems to be a valid package: ".$completePkgPath)->br();
            die();
        }

        // Trying to extract the pkg file to temporary directory
        $tmpDir = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR."tmpUpdateDir_".uniqid();
        while(is_dir($tmpDir) || is_file($tmpDir)) {
            $tmpDir = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR."tmpUpdateDir_".uniqid();
        }

        // Creating the temporary directory
        \KeyClass\Filetree::createDirectory($tmpDir, 777);

        // Extracting the file
        try {
            
            $phar = new PharData($completePkgPath);
            $phar->extractTo($tmpDir, null, true);
        } catch (Exception $e) {
            $climate->br();
            $climate->to('error')->red("The file seems to be corrupted: ".$completePkgPath)->br();
            die();
        }

        // Verifying if the package version is later than the installed version
        $controlFile = $tmpDir.DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."control.json";
        if (!file_exists($controlFile) || !is_readable($controlFile)) {
            stopInstallUpdate($tmpDir, "File not found or not readable: ".$controlFile);
        }

        // Trying to read the JSON file
        $jsonData = \KeyClass\JSON::getJSONDataFile($controlFile);
        if ($jsonData === false) {
            stopInstallUpdate($tmpDir, "Cannot read control file: ".$controlFile);
        }

        $missingInfoError=[];
        if (!isset($jsonData['package']) || trim($jsonData['package']) === ""){
            $missingInfoError[]="Information missing at control file: package";
        }
        if (!isset($jsonData['version']) || trim($jsonData['version']) === ""){
            $missingInfoError[]="Information missing at control file: version";
        }
        if ((!isset($jsonData['installed-size']) || trim($jsonData['installed-size']) === "")){
            $missingInfoError[]="Information missing at control file: installed-size";
        }
        if (!isset($jsonData['maintainer']) || trim($jsonData['maintainer']) === ""){
            $missingInfoError[]="Information missing at control file: maintainer";
        }
        if (!isset($jsonData['provides'])){
            $missingInfoError[]="Information missing at control file: provides";
        }
        if (!isset($jsonData['depends'])){
            $missingInfoError[]="Information missing at control file: depends";
        }
        if (!isset($jsonData['recommends'])){
            $missingInfoError[]="Information missing at control file: recommends";
        }
        if (!isset($jsonData['postinst-cmd'])){
            $missingInfoError[]="Information missing at control file: postinst-cmd";
        }
        if (!isset($jsonData['postrm-cmd'])){
            $missingInfoError[]="Information missing at control file: postrm-cmd";
        }
        if (!isset($jsonData['preinst-cmd'])){
            $missingInfoError[]="Information missing at control file: preinst-cmd";
        }
        if (!isset($jsonData['prerm-cmd'])){
            $missingInfoError[]="Information missing at control file: prerm-cmd";
        }
        if (!isset($jsonData['description']) || trim($jsonData['description']) === ""){
            $missingInfoError[]="Information missing at control file: description";
        }
        
        if (count($missingInfoError) > 0){
            stopInstallUpdate($tmpDir, implode("|",$missingInfoError));
        }

        $newPackage=$jsonData['package'];
        $newPackageVersion=$jsonData['version'];
        $newPackageSection=$jsonData['section'];

        // Checking if package is installed
        $installedItemInfo=json_decode($pkgController->getInstalledItemInfo($newPackage, $authorization, false), true);

        // If is installed, compare the current version with candidate version
        if ($installedItemInfo !== null){
            $installedVersionParts = \KeyClass\Registry::getVersionParts($installedItemInfo['version']);
            if ($installedVersionParts === false){
                stopInstallUpdate($tmpDir, "Wrong version of installed package ".$newPackage.": ".$installedItemInfo['version']);
            }

            $newPackageVersionParts = \KeyClass\Registry::getVersionParts($newPackageVersion);
            if ($newPackageVersionParts === false){
                stopInstallUpdate("Wrong version of new package ".$newPackage.": ".$newPackageVersion);
            }

            $installedVersionString = $pkgController->getVersionFromInfo($installedVersionParts);
            $newPackageVersionString = $pkgController->getVersionFromInfo($newPackageVersionParts);

            $state=version_compare($installedVersionString, $newPackageVersionString);
            switch(true){
                case $state < 0:
                break;
                case $state === 0:
                    $climate->br();
                    $input = $climate->to('out')->input('Installed package version equal to the new package. Do you want to overwrite (y / N)?')->accept(['s', 'S', 'y', 'Y', 'n', 'N']);
                    $response = $input->prompt();

                    switch (strtolower($response)){
                        case 'y':
                        case 's':
                        break;

                        case 'n':
                            stopInstallUpdate($tmpDir, "Aborting install");
                        break;
                    }
                break;
                case $state > 0:
                    $climate->br();
                    $input = $climate->to('out')->input('Version of installed package greater than new package. Do you wish to continue (y/N) ?')->accept(['s', 'S', 'y', 'Y', 'n', 'N']);
                    $response = $input->prompt();

                    switch (strtolower($response)){
                        case 'y':
                        case 's':
                        break;

                        case 'n':
                            stopInstallUpdate($tmpDir, "");
                        break;
                    }
                break;
                default:
                    stopInstallUpdate($tmpDir, "Error verifying package version ".$newPackage.": ".$newPackageVersion);
                break;
            }
        }
        
        // Copying registry files
        $newpackageDirectory = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."controls".DIRECTORY_SEPARATOR.$newPackage;
        if (!is_dir($newpackageDirectory)) {
            KeyClass\Filetree::createDirectory($newpackageDirectory, 777);
        }
        KeyClass\Filetree::copyDirectory($tmpDir.DIRECTORY_SEPARATOR."registry", $newpackageDirectory);
        
        $directory = null;
        if (strtolower($newPackageSection) === 'component'){
            $directory=$installedItemInfo['directory'];
        }
        
        // Registering new item
        $pkgController->registerItem($newPackageSection, $newPackage, $newPackageVersion, $directory);

        // Running the pre-install script
        $preInstallFile = $tmpDir.DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."preinst.php";
        if (file_exists($preInstallFile)) {
            KeyClass\Filetree::requireOnceFile($preInstallFile);
        }
        
        // Copying the files to the root
        KeyClass\Filetree::copyDirectory($tmpDir.DIRECTORY_SEPARATOR."data", INSTALL_DIR);

        // Running the pos-install script
        $posInstallFile = $tmpDir.DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."posinst.php";
        if (file_exists($posInstallFile)) {
            KeyClass\Filetree::requireOnceFile($posInstallFile);
        }

        // Erasing temporary directory
        KeyClass\Filetree::deleteDirectory($tmpDir);
        
        $climate->br();
        $climate->to('error')->blue("Package installed succefully: ".basename($completePkgPath))->br();
        die();
    break;

    case 'remove':
    case 'uninstall':
        $authorization = \KeyClass\Registry::getLocalAuthorization(REQUESTED_URL);
        
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

        $pkgController = \KeyClass\Request::Controller('sys::pkg');
        $localVersion = $pkgController->getInstalledItemInfo($target, $authorization);
        if ($localVersion === null) {
            \KeyClass\Error::errorRegister("The $section/$target is not installed");
        }
        else{
            die('to do: uninstall package');
        }
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