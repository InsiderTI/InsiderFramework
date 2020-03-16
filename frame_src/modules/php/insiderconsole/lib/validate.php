<?php
namespace Modules\insiderconsole;

/**
  Class of object used in insiderconsole
 
  @package \Modules\insiderconsole

  @author Marcello Costa
 */
class Validate {
    /**
        Get data from control file of package directory

        @author Marcello Costa

        @package Core

        @param  string  $controlFilePath    Path of control.json file

        @return  array  Data of control.json file
    */
    static public function getDataFromPackageControlFile (string $controlFilePath) : array {
        $jsonData = \KeyClass\JSON::getJSONDataFile($controlFilePath);
        if ($jsonData === false) {
            actionManager::stopInstallUpdate(null, "Cannot read control file: ".$controlFilePath);
        }
        
        return $jsonData;
    }

    /**
        Validate the tree directory of a package

        @author Marcello Costa

        @package Modules\insiderconsole

        @param  string  $packageDirectory    Directory to be validated

        @return  array Errors on validations. Empty array means no errors.
    */
    static public function validatePackageDirectoryTree(string $packageDirectory) : array {
        $directoriesValidate = array(
            0 => "registry",
            1 => "data",
        );
        $controlFiles = array(
            0 => "registry".DIRECTORY_SEPARATOR."control.json",
            1 => "registry".DIRECTORY_SEPARATOR."LICENSE",
            2 => "registry".DIRECTORY_SEPARATOR."CHANGELOG",
            3 => "registry".DIRECTORY_SEPARATOR."postinst.php",
            4 => "registry".DIRECTORY_SEPARATOR."postrm.php",
            5 => "registry".DIRECTORY_SEPARATOR."preinst.php",
            6 => "registry".DIRECTORY_SEPARATOR."prerm.php",
        );

        foreach($directoriesValidate as $dV){
            if (
                !is_dir($packageDirectory.DIRECTORY_SEPARATOR.$dV) || 
                !is_readable($packageDirectory.DIRECTORY_SEPARATOR.$dV)
                ){
                return false;
            }
        }

        foreach($controlFiles as $cF){
            if (!is_file($packageDirectory.DIRECTORY_SEPARATOR.$cF) ||
                !is_readable($packageDirectory.DIRECTORY_SEPARATOR.$cF)
                ){
                return false;
            }
        }

        // Validating control.json
        $jsonData = \Modules\insiderconsole\Validate::getDataFromPackageControlFile($packageDirectory.DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."control.json");

        $missingInfoError=[];
        if (!isset($jsonData['package']) || trim($jsonData['package']) === ""){
            $missingInfoError[]="Information missing at control file: package";
        }
        if (!isset($jsonData['version']) || trim($jsonData['version']) === ""){
            $missingInfoError[]="Information missing at control file: version";
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
        if (!isset($jsonData['description']) || trim($jsonData['description']) === ""){
            $missingInfoError[]="Information missing at control file: description";
        }
        
        return $missingInfoError;
    }
}