<?php
namespace Modules\insiderconsole;

/**
  Class of object used in insiderconsole
 
  @package \Modules\insiderconsole

  @author Marcello Costa
 */
class Build {
    /**
        Builds an package file from a directory tree package

        @author Marcello Costa

        @package Modules\insiderconsole

        @param   object  $climate   Climate object
        @param   array   $argumentsAndDependencies   Arguments and dependencies sent by the user

        @return  bool  Return of operation
    */
    static public function buildPackage(&$climate, array $argumentsAndDependencies) : bool {
        $origDestTarget = $argumentsAndDependencies['actionValue'];
        if (trim($origDestTarget) === ""){
            $input = $climate->input("Build Directory:");
            $buildDirectory = $input->prompt();
            if (trim($buildDirectory) === ""){
                $climate->br()->to('error')->write("Action cancelled")->br();
                die();
            }
            
            $input = $climate->input("Package file:");
            $packageFile = $input->prompt();
            if (trim($packageFile) === ""){
                $climate->br()->to('error')->write("Action cancelled")->br();
                die();
            }
        }
        else{
            // String validation for the source directory and the final package file
            if (strpos($origDestTarget, "::") === false) {
                \KeyClass\Error::errorRegister('Invalid syntax. Check --help for the command usage');
            }
            $dataExp = explode('::',$origDestTarget);
            if (count($dataExp) !== 2) {
                \KeyClass\Error::errorRegister('Invalid syntax. Check --help for the command usage');
            }
            
            $buildDirectory = $dataExp[0];
            $packageFile = $dataExp[1];
        }
        
        // Checking if it's an valid build directory
        $validationErrors = \Modules\insiderconsole\Validate::validatePackageDirectoryTree($buildDirectory);
        if(count($validationErrors) !== 0){
            \KeyClass\Error::errorRegister(implode($validationErrors, " | "));
        }
        
        // Getting version from controle file
        $jsonData = \Modules\insiderconsole\Validate::getDataFromPackageControlFile($buildDirectory.DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."control.json");
        
        // Removing extension
        $ext = pathinfo($packageFile, PATHINFO_EXTENSION);
        if ($ext !== ""){
            $packageFile = substr($packageFile, 0, strlen($packageFile)-4);
        }

        // Adding version + extension
        $packageFile .= "-".$jsonData['version']; 
  
        $compressedPathFile = \KeyClass\FileTree::compressDirectoryOrFile($buildDirectory, $packageFile);
        $finalPackageFileName = substr($packageFile, 0, strlen($compressedPathFile)-4).".pkg";
        \KeyClass\FileTree::renameFile($compressedPathFile, $finalPackageFileName, true);

        $climate->br()->write("Package file builded: ".$finalPackageFileName)->br();
        
        return true;
    }
}