<?php

namespace Modules\InsiderFramework\Core\PackageSystem\Console;

/**
 * Class of object used in Console
 *
 * @package Modules\InsiderFramework\Core\PackageSystem\Console\DirectoryTreeGenerator
 *
 * @author Marcello Costa
 */
class DirectoryTreeGenerator
{
    /**
     * Generate a directory tree package
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\PackageSystem\Console\DirectoryTreeGenerator
     *
     * @param object $climate Climate object
     *
     * @return void
    */
    public static function generate(
        $climate
    ): void {
        $package = $climate->arguments->get('package');
        $destinationDirectory = $climate->arguments->get('destinationDirectory');
        $version = $climate->arguments->get('version');
        $authors = $climate->arguments->get('authors');
        $description = $climate->arguments->get('description');
        $section = $climate->arguments->get('section');

        // Data of control file
        $controlData = [];
        
        if ($package . "" === "") {
            $input = $climate->input("Package name:");
            $package = $input->prompt();
            
            if (trim($package) === "") {
                $climate->br()->to('error')->write("Package must have a name")->br();
                die();
            }
        } else {
            $climate->br()->write("Package name: $package")->br();
        }
        $controlData['package'] = $package;

        if ($destinationDirectory . "" === "") {
            $input = $climate->input("Destination directory:");
            $destinationDirectory = $input->prompt();
            
            if (trim($destinationDirectory) === "") {
                $climate->br()->to('error')->write("Destination directory must be specified")->br();
                die();
            }
        }

        $finalDestinationDirectory = INSTALL_DIR . DIRECTORY_SEPARATOR . $destinationDirectory;
        $climate->br()->write("Destination directory: $finalDestinationDirectory")->br();

        if ($version . "" === "") {
            $input = $climate->input("Version [1.0.0]:");
            $version = $input->prompt();
            if (trim($version) === "") {
                $version = "1.0.0";
            }
        } else {
            $climate->br()->write("Version: $version")->br();
        }

        // If typed version is not valid
        $validation = \Modules\InsiderFramework\Core\Registry::validateVersionSyntax($version);

        // Error
        if (!$validation) {
            $climate->br()->to('error')->write("Invalid version. Must be Semantic Versioning (MAJOR.MINOR.PATCH-OPTIONAL)")->br();
            die();
        }

        $controlData['version'] = $version;

        if ($authors . "" === "") {
            $input = $climate->input("Authors:");
            $authors = $input->prompt();
            if (trim($authors) === "") {
                $climate->br()->to('error')->write("Authors must be specified")->br();
                die();
            }
        } else {
            $climate->br()->write("Authors: $authors")->br();
        }

        $controlData['authors'] = $authors;

        if ($description . "" === "") {
            $input = $climate->input("Description:");
            $description = $input->prompt();
            if (trim($description) === "") {
                $climate->br()->to('error')->write("Description must be specified")->br();
                die();
            }
        } else {
            $climate->br()->write("Description: $description")->br();
        }
        $controlData['description'] = $description;

        $sectionOptions = ['Guild', 'Module', 'App', 'SagaciousComponent'];
        if ($section . "" === "") {
            $input = $climate->radio('Choose section of package:', $sectionOptions);
            $section = $input->prompt();
            $controlData['section'] = strtolower($section);
        } else {
            if (!in_array(ucwords($section), $sectionOptions)) {
                $climate->br()->to('error')->write("Cannot recognize section $section. Valid sections are: " . implode(", ", $sectionOptions))->br();
                die();
            }
            $controlData['section'] = strtolower($section);
        }

        // Set empty values for these properties
        $controlData["provides"] = [];
        $controlData["depends"] = [];
        $controlData["recommends"] = [];

        \Modules\InsiderFramework\Core\PackageSystem\Console\DirectoryTreeGenerator::writePackageDirTree($destinationDirectory, $controlData);

        $climate->out("\nPackage directory tree created. After making all changes, run build command\n");
    }
    
    /**
     * Write files and directories of the package directory tree
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\PackageSystem\Console\DirectoryTreeGenerator
     *
     * @param string $destinationDirectory Destination of directory tree
     * @param array  $controlData    Data of control file
     *
     * @return void
    */
    public static function writePackageDirTree(string $destinationDirectory, array $controlData): void
    {
        // Creating package directory
        if (!\Modules\InsiderFramework\Core\FileTree::createDirectory($destinationDirectory, 777)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister('Unable to create ' . $destinationDirectory);
        }
        
        // Creating control and data directories
        \Modules\InsiderFramework\Core\FileTree::createDirectory(
            $destinationDirectory . DIRECTORY_SEPARATOR .
            "Registry",
            777
        );
        \Modules\InsiderFramework\Core\FileTree::createDirectory($destinationDirectory . DIRECTORY_SEPARATOR . "Data", 777);
        
        // Creating default files
        $controlDir = $destinationDirectory . DIRECTORY_SEPARATOR .
                      "Registry" . DIRECTORY_SEPARATOR;

        \Modules\InsiderFramework\Core\FileTree::fileWriteContent($controlDir . "CHANGELOG", "", true);
        \Modules\InsiderFramework\Core\FileTree::fileWriteContent($controlDir . "LICENSE", "", true);
        \Modules\InsiderFramework\Core\FileTree::fileWriteContent($controlDir . "Postinst.php", "", true);
        \Modules\InsiderFramework\Core\FileTree::fileWriteContent($controlDir . "Postrm.php", "", true);
        \Modules\InsiderFramework\Core\FileTree::fileWriteContent($controlDir . "Preinst.php", "", true);
        \Modules\InsiderFramework\Core\FileTree::fileWriteContent($controlDir . "Prerm.php", "", true);
        
        // Creating control.json file
        \Modules\InsiderFramework\Core\FileTree::fileWriteContent($controlDir . "Control.json", json_encode($controlData), true);
    }
}
