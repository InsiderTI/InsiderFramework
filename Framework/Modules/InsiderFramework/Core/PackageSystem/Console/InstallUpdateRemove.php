<?php

namespace Modules\InsiderFramework\Core\PackageSystem\Console;

class InstallUpdateRemove {

    /**
    * Install or Update a package
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\PackageSystem\Console\InstallUpdateRemove
    *
    * @param object $climate Climate object
    *
    * @return void
    */
    public static function installOrUpdate($climate){
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
        
        $pkgController = new \Apps\Sys\Controllers\PkgController();
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
        $tmpDir = INSTALL_DIR . DIRECTORY_SEPARATOR . "Framework" . DIRECTORY_SEPARATOR .
                  "Cache" . DIRECTORY_SEPARATOR . "tmpUpdateDir_" . uniqid();

        while (is_dir($tmpDir) || is_file($tmpDir)) {
            $tmpDir = INSTALL_DIR . DIRECTORY_SEPARATOR . "Framework" . DIRECTORY_SEPARATOR . "Cache" .
            DIRECTORY_SEPARATOR . "tmpUpdateDir_" . uniqid();
        }

        // Creating the temporary directory
        \Modules\InsiderFramework\Core\FileTree::createDirectory($tmpDir, 777);

        // Extracting the file
        try {
            \Modules\InsiderFramework\Core\Filetree::decompressFile($completePkgPath, $tmpDir, "zip");
        } catch (Exception $e) {
            $climate->br();
            $climate->to('error')->red("The file seems to be corrupted: " . $completePkgPath)->br();
            die();
        }

        // Verifying if the package version is later than the installed version
        $controlFile = $tmpDir . DIRECTORY_SEPARATOR . "Registry" . DIRECTORY_SEPARATOR . "Control.json";
        
        $packageControlData = new \Modules\InsiderFramework\Core\Registry\Definition\PackageControlData($controlFile);
        
        $newPackage = $packageControlData->getPackage();
        $newPackageVersion = $packageControlData->getVersion();
        $newPackageSection = $packageControlData->getSection();

        // Checking if package is installed
        $installedItemInfo = \Modules\InsiderFramework\Core\Registry::getItemInfo($newPackage);
        
        // If is installed, compare the current version with candidate version
        if ($installedItemInfo['version'] !== '0.0.0') {
            $installedVersionParts = \Modules\InsiderFramework\Core\Registry::getVersionParts(
                $installedItemInfo['version']
            );
            if ($installedVersionParts === false) {
                \Modules\InsiderFramework\Core\PackageSystem\Console\InstallUpdateRemove::stopInstallUpdate(
                    $tmpDir,
                    "Wrong version of installed package " . $newPackage . ": " . $installedItemInfo['version']
                );
            }

            $newPackageVersionParts = \Modules\InsiderFramework\Core\Registry::getVersionParts($newPackageVersion);
            if ($newPackageVersionParts === false) {
                \Modules\InsiderFramework\Core\PackageSystem\Console\InstallUpdateRemove::stopInstallUpdate("Wrong version of new package " . $newPackage . ": " . $newPackageVersion);
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
                            \Modules\InsiderFramework\Core\PackageSystem\Console\InstallUpdateRemove::stopInstallUpdate($tmpDir, "Aborting install");
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
                            \Modules\InsiderFramework\Core\PackageSystem\Console\InstallUpdateRemove::stopInstallUpdate($tmpDir, "");
                            break;
                    }
                    break;
                default:
                    \Modules\InsiderFramework\Core\PackageSystem\Console\InstallUpdateRemove::stopInstallUpdate(
                        $tmpDir,
                        "Error verifying package version " . $newPackage . ": " . $newPackageVersion
                    );
                    break;
            }
        }
     
        // Copying registry files
        $newpackageDirectory = INSTALL_DIR . DIRECTORY_SEPARATOR . "Framework" .
                               DIRECTORY_SEPARATOR . "Registry" . DIRECTORY_SEPARATOR .
                               "Controls" . DIRECTORY_SEPARATOR . strtolower($newPackage);

        // Running the pre-install script
        $preInstallFile = $tmpDir . DIRECTORY_SEPARATOR . "Registry" . DIRECTORY_SEPARATOR . "Preinst.php";
        if (file_exists($preInstallFile)) {
            \Modules\InsiderFramework\Core\FileTree::requireOnceFile($preInstallFile);
        }
        
        // Copying the files to the root
        \Modules\InsiderFramework\Core\FileTree::copyDirectory($tmpDir . DIRECTORY_SEPARATOR . "Data", INSTALL_DIR);

        // Running the pos-install script
        $posInstallFile = $tmpDir . DIRECTORY_SEPARATOR . "Registry" . DIRECTORY_SEPARATOR . "Posinst.php";
        if (file_exists($posInstallFile)) {
            \Modules\InsiderFramework\Core\FileTree::requireOnceFile($posInstallFile);
        }

        // Copying the control files to registry
        if (!is_dir($newpackageDirectory)) {
            \Modules\InsiderFramework\Core\FileTree::createDirectory($newpackageDirectory, 777);
        }
        \Modules\InsiderFramework\Core\FileTree::copyDirectory(
            $tmpDir . DIRECTORY_SEPARATOR . "Registry",
            $newpackageDirectory
        );

        // Registering new item
        \Modules\InsiderFramework\Core\Registry::registerItem($packageControlData);

        // Erasing temporary directory
        \Modules\InsiderFramework\Core\FileTree::deleteDirectory($tmpDir);
        
        $climate->br();
        $climate->to('error')->blue("Package installed succefully: " . basename($completePkgPath))->br();
        die();
    }

    /**
    * Remove a package
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\PackageSystem\Console\InstallUpdateRemove
    *
    * @param object $climate Climate object
    *
    * @return void
    */
    public static function remove($climate): void {
        $package = $climate->arguments->get('package');
        if ($package === "") {
            $climate->br();
            $climate->to('error')->red("Syntax error: package must be specified")->br();
            $climate->to('error')->write("Type <light_blue>console.php --help</light_blue> for help")->br();
            die();
        }

        $localVersion = \Modules\InsiderFramework\Core\Registry\Manipulation\Registry::getItemInfo($package);

        if ($localVersion['version'] === '0.0.0') {
            $climate->br();
            $climate->to('error')->red("The $package is not installed")->br();
            die();
        }

        // Running the pre-uninstall script
        $preUninstallFile = INSTALL_DIR.DIRECTORY_SEPARATOR."Framework".DIRECTORY_SEPARATOR."Registry".DIRECTORY_SEPARATOR."Prerm.php";
        if (file_exists($preUninstallFile)) {
            \Modules\InsiderFramework\Core\FileTree::requireOnceFile($preUninstallFile);
        }

        $directories = [];
        $dataDirPattern = "Data" . DIRECTORY_SEPARATOR;
        $registryDirPattern = "Registry" . DIRECTORY_SEPARATOR;
        foreach($localVersion['md5sum'] as $file => $md5){
            if (strpos($file, $dataDirPattern) !== false){
                $correctPath = str_replace($dataDirPattern, "", $file);
            } else {
                $correctPath = str_replace($registryDirPattern, 
                "Framework" . DIRECTORY_SEPARATOR . 
                "Registry" . DIRECTORY_SEPARATOR .
                "Controls" . DIRECTORY_SEPARATOR . 
                $localVersion['package'] . DIRECTORY_SEPARATOR,
                $file);
            }

            \Modules\InsiderFramework\Core\FileTree::deleteFile(
                $correctPath
            );

            $dirpath = dirname($correctPath);
            if (!in_array($dirpath, $directories)){
                $directories[]=$dirpath;
            }
        }

        var_dump('HERE');
        die("FILE: " . __FILE__ . "<br/>LINE: " . __LINE__);
        $notEmptyDirectories = [];
        foreach($directories as $dir){
            $empty = \Modules\InsiderFramework\Core\Validation\FileTree::isDirEmpty($dir);
            if ($empty){
                \Modules\InsiderFramework\Core\FileTree::deleteDirectory(
                    $dir
                );  
            } else {
                $notEmptyDirectories = $dir;
            }
        }

        // Running the pos-uninstall script
        $posUninstallFile = INSTALL_DIR.DIRECTORY_SEPARATOR."Framework".DIRECTORY_SEPARATOR."Registry".DIRECTORY_SEPARATOR."Posrm.php";
        if (file_exists($posUninstallFile)) {
            \Modules\InsiderFramework\Core\FileTree::requireOnceFile($posUninstallFile);
        }

        // Remove package from registry of modules
        
    }

    /**
     * Function that interrupts the update process or installs a package
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
        \Modules\InsiderFramework\Core\FileTree::deleteDirectory($tmpDir);

        // Showing the error
        $climate->br();
        $climate->to('error')->red($message)->br();

        // Stopping script
        die();
    }
}