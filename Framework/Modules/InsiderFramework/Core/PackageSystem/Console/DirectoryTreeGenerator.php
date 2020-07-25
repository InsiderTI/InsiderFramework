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
     * @param object $console Modules\InsiderFramework\Console\Adapter object
     *
     * @return void
    */
    public static function generate(
        $console
    ): void {
        $package = $console->arguments->get('package');
        $destinationDirectory = $console->arguments->get('destinationDirectory');
        $version = $console->arguments->get('version');
        $authors = $console->arguments->get('authors');
        $description = $console->arguments->get('description');
        $section = $console->arguments->get('section');

        // Data of control file
        $controlData = [];
        
        if ($package . "" === "") {
            $input = $console->input("Package name:");
            $package = $input->prompt();
            
            if (trim($package) === "") {
                $console->br();
                $console->setOutput('error');
                $console->write("Package must have a name");
                $console->br();
                die();
            }
        } else {
            $console->br();
            $console->write("Package name: $package");
            $console->br();
        }
        $controlData['package'] = $package;

        if ($destinationDirectory . "" === "") {
            $input = $console->input("Destination directory:");
            $destinationDirectory = $input->prompt();
            
            if (trim($destinationDirectory) === "") {
                $console->br();
                $console->setOutput('error');
                $console->write("Destination directory must be specified");
                $console->br();
                die();
            }
        }

        $finalDestinationDirectory = INSTALL_DIR . DIRECTORY_SEPARATOR . $destinationDirectory;
        $console->br();
        $console->write("Destination directory: $finalDestinationDirectory");
        $console->br();

        if ($version . "" === "") {
            $input = $console->input("Version [1.0.0]:");
            $version = $input->prompt();
            if (trim($version) === "") {
                $version = "1.0.0";
            }
        } else {
            $console->br();
            $console->write("Version: $version");
            $console->br();
        }

        // If typed version is not valid
        $validation = \Modules\InsiderFramework\Core\Registry::validateVersionSyntax($version);

        // Error
        if (!$validation) {
            $console->br();
            $console->setOutput('error');
            $console->write(
                "Invalid version. Must be Semantic Versioning (MAJOR.MINOR.PATCH-OPTIONAL)"
            );
            $console->br();
            die();
        }

        $controlData['version'] = $version;

        if ($authors . "" === "") {
            $input = $console->input("Authors:");
            $authors = $input->prompt();
            if (trim($authors) === "") {
                $console->br();
                $console->setOutput('error');
                $console->write("Authors must be specified");
                $console->br();
                die();
            }
        } else {
            $console->br();
            $console->write("Authors: $authors");
            $console->br();
        }

        $controlData['authors'] = $authors;

        if ($description . "" === "") {
            $input = $console->input("Description:");
            $description = $input->prompt();
            if (trim($description) === "") {
                $console->br();
                $console->setOutput('error');
                $console->write("Description must be specified");
                $console->br();
                die();
            }
        } else {
            $console->br();
            $console->write("Description: $description");
            $console->br();
        }
        $controlData['description'] = $description;

        $sectionOptions = ['Guild', 'Module', 'App', 'SagaciousComponent'];
        if ($section . "" === "") {
            $input = $console->radio('Choose section of package:', $sectionOptions);
            $section = $input->prompt();
            $controlData['section'] = strtolower($section);
        } else {
            if (!in_array(ucwords($section), $sectionOptions)) {
                $console->br();
                $console->setOutput('error');
                $console->write(
                    "Cannot recognize section $section. Valid sections are: " . implode(", ", $sectionOptions)
                );
                $console->br();
                die();
            }
            $controlData['section'] = strtolower($section);
        }

        // Set empty values for these properties
        $controlData["provides"] = [];
        $controlData["depends"] = [];
        $controlData["recommends"] = [];

        \Modules\InsiderFramework\Core\PackageSystem\Console\DirectoryTreeGenerator::writePackageDirTree(
            $destinationDirectory,
            $controlData
        );

        $console->write("\nPackage directory tree created. After making all changes, run build command\n");
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
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                'Unable to create ' . $destinationDirectory
            );
        }
        
        // Creating control and data directories
        \Modules\InsiderFramework\Core\FileTree::createDirectory(
            $destinationDirectory . DIRECTORY_SEPARATOR .
            "Registry",
            777
        );
        \Modules\InsiderFramework\Core\FileTree::createDirectory(
            $destinationDirectory . DIRECTORY_SEPARATOR . "Data",
            777
        );
        
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
        \Modules\InsiderFramework\Core\FileTree::fileWriteContent(
            $controlDir . "Control.json",
            json_encode($controlData),
            true
        );
    }
}
