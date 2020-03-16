<?php
namespace Modules\insiderconsole;

/**
  Class of object used in insiderconsole
 
  @package \Modules\insiderconsole

  @author Marcello Costa
 */
class DirectoryTreeGenerator {
    /** 
        Generate a directory tree package

        @author Marcello Costa

        @package Modules\insiderconsole

        @param   object  $climate                    Climate object
        @param   array   $argumentsAndDependencies   Arguments and dependencies sent by the user

        @return  void
    */
    static public function generate(&$climate, array $argumentsAndDependencies) {
        // Data of control file
        $controlData = [];

        $packageName = $argumentsAndDependencies['actionValue'];
        if (trim($packageName) !== ""){
            $climate->out("Package Name: ".$packageName);
        }
        else{
            $input = $climate->input("Package Name:");
            $packageName = $input->prompt();
            if (trim($packageName) === ""){
                $climate->br()->to('error')->write("Action cancelled")->br();
                die();
            }
        }
        $controlData['package']=$packageName;

        $destinationDir = INSTALL_DIR.DIRECTORY_SEPARATOR.$packageName;
        $input = $climate->input("Directory destination [$destinationDir]:");
        $destinationDirtmp = $input->prompt();
        if (trim($destinationDirtmp) !== ""){
            $destinationDir = $destinationDirtmp;
        }

        $input = $climate->input("Version:");
        $version = $input->prompt();
        if (trim($version) === ""){
            $climate->br()->to('error')->write("Action cancelled")->br();
            die();
        }
        // Validate version type
        else{
            // If typed version is not valid
            $validation = \KeyClass\Registry::validateVersionSyntax($version);

            // Error
            if (!$validation){
                $climate->br()->to('error')->write("Invalid version. Must be Semantic Versioning (MAJOR.MINOR.PATCH-OPTIONAL)")->br();
                die();
            }
        }
        $controlData['version']=$version;

        $input = $climate->input("Authors:");
        $authors = $input->prompt();
        $controlData['authors']=$authors;

        $input = $climate->input("Description:");
        $description = $input->prompt();
        $controlData['description']=$description;

        $options = ['Guild', 'Module', 'Pack', 'Component'];
        $input = $climate->radio('Choose section of package:', $options);
        $section = $input->prompt();
        $controlData['section']=strtolower($section);

        // Set empty values for these properties
        $controlData["provides"]=[];
        $controlData["depends"]=[];
        $controlData["recommends"]=[];

        \Modules\insiderconsole\DirectoryTreeGenerator::writePackageDirTree($destinationDir, $controlData);

        $climate->out("\nPackage directory tree created. After making all changes, run build command\n");
    }
    
    /**
        Write files and directories of the package directory tree

        @author Marcello Costa

        @package Modules\insiderconsole

        @param  string $destinationDir   Destination of directory tree
        @param  array  $controlData      Data of control file

        @return  void
    */
    static public function writePackageDirTree(string $destinationDir, array $controlData) : void {
        // Creating package directory
        if (!\KeyClass\FileTree::createDirectory($destinationDir, 777)){
            \KeyClass\Error::errorRegister('Unable to create '.$destinationDir);
        }
        
        // Creating control and data directories
        \KeyClass\FileTree::createDirectory($destinationDir.DIRECTORY_SEPARATOR."registry", 777);
        \KeyClass\FileTree::createDirectory($destinationDir.DIRECTORY_SEPARATOR."data", 777);
        
        // Creating default files
        $controlDir = $destinationDir.DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR;
        \KeyClass\FileTree::fileWriteContent($controlDir."CHANGELOG", "", true);
        \KeyClass\FileTree::fileWriteContent($controlDir."LICENSE", "", true);
        \KeyClass\FileTree::fileWriteContent($controlDir."postinst.php", "", true);
        \KeyClass\FileTree::fileWriteContent($controlDir."postrm.php", "", true);
        \KeyClass\FileTree::fileWriteContent($controlDir."preinst.php", "", true);
        \KeyClass\FileTree::fileWriteContent($controlDir."prerm.php", "", true);
        
        // Creating control.json file
        \KeyClass\FileTree::fileWriteContent($controlDir."control.json", json_encode($controlData), true);
    }
}