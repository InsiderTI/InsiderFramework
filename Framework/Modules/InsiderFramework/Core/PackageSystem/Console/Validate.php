<?php

namespace Modules\InsiderFramework\Core\PackageSystem\Console;

/**
 * Class of object used in insiderconsole
 *
 * @author Marcello Costa
 *
 * @package \Modules\InsiderFramework\Core\PackageSystem\Console\Validate
 */
class Validate
{
    /**
     * Get data from control file of package directory
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\PackageSystem\Console\Validate
     *
     * @param string $controlFilePath Path of control.json file
     *
     * @return array Data of control.json file
    */
    public static function getDataFromPackageControlFile(string $controlFilePath): array
    {
        $jsonData = \Modules\InsiderFramework\Core\Json::getJSONDataFile($controlFilePath);
        if ($jsonData === false) {
            return [];
        }
        
        return $jsonData;
    }

   /**
    * Validate the tree directory of a package
    *
    * @author Marcello Costa
    *
    * @package \Modules\InsiderFramework\Core\PackageSystem\Console\Validate
    *
    * @param string $packageDirectory Directory to be validated
    *
    * @return array Return of validation (filled with errors)
    */
    public static function validatePackageDirectoryTree(string $packageDirectory): array
    {
        $directoriesValidate = array(
            0 => "Registry",
            1 => "Data",
        );
        $controlFiles = array(
            0 => "Registry" . DIRECTORY_SEPARATOR . "Control.json",
            1 => "Registry" . DIRECTORY_SEPARATOR . "LICENSE",
            2 => "Registry" . DIRECTORY_SEPARATOR . "CHANGELOG",
            3 => "Registry" . DIRECTORY_SEPARATOR . "Postinst.php",
            4 => "Registry" . DIRECTORY_SEPARATOR . "Postrm.php",
            5 => "Registry" . DIRECTORY_SEPARATOR . "Preinst.php",
            6 => "Registry" . DIRECTORY_SEPARATOR . "Prerm.php",
        );

        foreach ($directoriesValidate as $dV) {
            if (
                !is_dir($packageDirectory . DIRECTORY_SEPARATOR . $dV) ||
                !is_readable($packageDirectory . DIRECTORY_SEPARATOR . $dV)
            ) {
                return false;
            }
        }

        foreach ($controlFiles as $cF) {
            if (
                !is_file($packageDirectory . DIRECTORY_SEPARATOR . $cF) ||
                !is_readable($packageDirectory . DIRECTORY_SEPARATOR . $cF)
            ) {
                return false;
            }
        }

        // Validating control.json
        $jsonData = \Modules\InsiderFramework\Core\PackageSystem\Console\Validate::getDataFromPackageControlFile(
            $packageDirectory . DIRECTORY_SEPARATOR .
            "Registry" . DIRECTORY_SEPARATOR .
            "Control.json"
        );

        $missingInfoError = [];
        if (!isset($jsonData['package']) || trim($jsonData['package']) === "") {
            $missingInfoError[] = "Information missing at control file: package";
        }
        if (!isset($jsonData['version']) || trim($jsonData['version']) === "") {
            $missingInfoError[] = "Information missing at control file: version";
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
        if (!isset($jsonData['description']) || trim($jsonData['description']) === "") {
            $missingInfoError[] = "Information missing at control file: description";
        }
        
        return $missingInfoError;
    }
}
