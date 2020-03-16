<?php
namespace Modules\insiderconsole;

/**
  Class of object used in insiderconsole
 
  @package \Modules\insiderconsole

  @author Marcello Costa
 */
class PackageManager {
    /**
        Install/Update packages

        @author Marcello Costa

        @package Modules\insiderconsole

        @param  object  $climate                     Climate object
        @param  array   $argumentsAndDependencies   Arguments and dependencies sent by the user

        @return  void
    */
    static public function installUpdatePackage(&$climate, array $argumentsAndDependencies) : void {
        $target = $argumentsAndDependencies['actionValue'];

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
            $zip = new \ZipArchive;
            if ($zip->open($completePkgPath) === TRUE) {
                $zip->extractTo($tmpDir);
                $zip->close();
            } else {
                $climate->to('error')->red("The file seems to be corrupted: ".$completePkgPath)->br();
            }
        } catch (\Exception $e) {
            $climate->br();
            $climate->to('error')->red("The file seems to be corrupted: ".$completePkgPath)->br();
        }

        // Verifying if the package version is later than the installed version
        $controlFile = $tmpDir.DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."control.json";
        if (!file_exists($controlFile) || !is_readable($controlFile)) {
            actionManager::stopInstallUpdate($tmpDir, "File not found or not readable: ".$controlFile);
        }

        // Trying to read the JSON file
        $jsonData = \Modules\insiderconsole\Validate::getDataFromPackageControlFile($controlFile, $tmpDir);

        // No information on JSON file
        if (count($jsonData) === 0){
            actionManager::stopInstallUpdate($tmpDir, "Cannot read JSON file ".$controlFile);
        }

        $newPackage=$jsonData['package'];
        $newPackageVersion=$jsonData['version'];
        $newPackageSection=$jsonData['section'];

        // Checking if package is installed
        $installedItemInfo=json_decode($pkgController->getInstalledPackageInfo($newPackage, $authorization, false), true);

        // If is installed, compare the current version with candidate version
        if ($installedItemInfo !== null){
            $installedVersionParts = \KeyClass\Registry::getVersionParts($installedItemInfo['version']);
            if ($installedVersionParts === false){
                actionManager::stopInstallUpdate($tmpDir, "Wrong version of installed package ".$newPackage.": ".$installedItemInfo['version']);
            }

            $newPackageVersionParts = \KeyClass\Registry::getVersionParts($newPackageVersion);
            if ($newPackageVersionParts === false){
                actionManager::stopInstallUpdate("Wrong version of new package ".$newPackage.": ".$newPackageVersion);
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
                            actionManager::stopInstallUpdate($tmpDir, "Aborting install");
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
                            actionManager::stopInstallUpdate($tmpDir, "");
                        break;
                    }
                break;
                default:
                    actionManager::stopInstallUpdate($tmpDir, "Error verifying package version ".$newPackage.": ".$newPackageVersion);
                break;
            }
        }

        // Copying registry files
        $newpackageDirectory = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."controls".DIRECTORY_SEPARATOR.$newPackage;
        if (!is_dir($newpackageDirectory)) {
            \KeyClass\Filetree::createDirectory($newpackageDirectory, 777);
        }
        \KeyClass\Filetree::copyDirectory($tmpDir.DIRECTORY_SEPARATOR."registry", $newpackageDirectory);

        $directory = null;
        if (strtolower($newPackageSection) === 'component'){
            $directory=$installedItemInfo['directory'];
        }

        // Registering new item
        $pkgController->registerPackage($newPackageSection, $newPackage, $newPackageVersion, $directory);

        // Running the pre-install script
        $preInstallFile = $tmpDir.DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."preinst.php";
        if (file_exists($preInstallFile)) {
            \KeyClass\Filetree::requireOnceFile($preInstallFile);
        }

        // Copying the files to the root
        \KeyClass\Filetree::copyDirectory($tmpDir.DIRECTORY_SEPARATOR."data", INSTALL_DIR);

        // Running the pos-install script
        $posInstallFile = $tmpDir.DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."posinst.php";
        if (file_exists($posInstallFile)) {
            \KeyClass\Filetree::requireOnceFile($posInstallFile);
        }

        // Erasing temporary directory
        \KeyClass\Filetree::deleteDirectory($tmpDir);

        $climate->br();
        $climate->blue("Package installed succefully: ".basename($completePkgPath))->br();
        die();
    }
    
    /**
        Removes a package

        @author Marcello Costa

        @package Modules\insiderconsole

        @param   object  $climate   Climate object
        @param   array   $argumentsAndDependencies   Arguments and dependencies sent by the user

        @return  bool  Return of operation
    */
    static public function removePackage($climate, array $argumentsAndDependencies) : bool {
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
        $localVersion = $pkgController->getInstalledPackageInfo($target, $authorization);
        if ($localVersion === null) {
            \KeyClass\Error::errorRegister("The $section/$target is not installed");
        }
        else{
            die('to do: uninstall package');
        }
    }
}