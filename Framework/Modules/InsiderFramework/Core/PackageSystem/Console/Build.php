<?php

namespace Modules\InsiderFramework\Core\PackageSystem\Console;

/**
 * Class of object used in insiderconsole
 *
 * @package \Modules\InsiderFramework\Core\PackageSystem\Console\Build
 *
 * @author Marcello Costa
 */
class Build
{
    /**
     * Builds an package file from a directory tree package
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\PackageSystem\Console\Build
     *
     * @param object $climate Climate object
     *
     * @return bool Return of operation
    */
    public static function buildPackage($climate): bool
    {
        $sourceDirectory = $climate->arguments->get('sourceDirectory');
        if (trim($sourceDirectory) === "") {
            $input = $climate->input("Build Directory:");
            $sourceDirectory = $input->prompt();
            if (trim($sourceDirectory) === "") {
                $climate->br()->to('error')->write("Action cancelled")->br();
                die();
            }
        }

        $packageFile = $climate->arguments->get('output');
        if (trim($packageFile) === "") {
            $input = $climate->input("Output package file:");
            $packageFile = $input->prompt();
            if (trim($packageFile) === "") {
                $climate->br()->to('error')->write("Action cancelled")->br();
                die();
            }
        }
        
        // Checking if it's an valid build directory
        \Modules\InsiderFramework\Core\PackageSystem\Validate::validatePackageDirectoryTree($sourceDirectory);

        // Getting version from controle file
        $packageControlData = $packageControlData = new \Modules\InsiderFramework\Core\Registry\Definition\PackageControlData(
            $sourceDirectory . DIRECTORY_SEPARATOR .
            "Registry" . DIRECTORY_SEPARATOR .
            "Control.json"
        );

        // Generating md5
        $md5filepath = $sourceDirectory . DIRECTORY_SEPARATOR .
            "Registry" . DIRECTORY_SEPARATOR .
            "Md5sum.json";

        if (file_exists($md5filepath)) {
            \Modules\InsiderFramework\Core\Filetree::deleteFile($md5filepath);
        }

        $dataMd5Files = \Modules\InsiderFramework\Core\Filetree::generateMd5DirTree(
            $sourceDirectory . DIRECTORY_SEPARATOR . "Data",
            true
        );

        $registryMd5Files = \Modules\InsiderFramework\Core\Filetree::generateMd5DirTree(
            $sourceDirectory . DIRECTORY_SEPARATOR . "Registry",
            true
        );

        \Modules\InsiderFramework\Core\Json::setJSONDataFile(
            array_merge($dataMd5Files, $registryMd5Files),
            $md5filepath,
            true
        );

        // Removing extension
        $ext = pathinfo($packageFile, PATHINFO_EXTENSION);
        if ($ext !== "") {
            $packageFile = substr($packageFile, 0, strlen($packageFile) - 4);
        }

        // Adding version + extension
        $packageFile .= "-" . $packageControlData->getVersion();
  
        $compressedPathFile = \Modules\InsiderFramework\Core\Filetree::compressDirectoryOrFile($sourceDirectory, $packageFile, "zip");
        $finalPackageFileName = substr($packageFile, 0, strlen($compressedPathFile) - 4) . ".pkg";
        \Modules\InsiderFramework\Core\Filetree::renameFile($compressedPathFile, $finalPackageFileName, true);

        $climate->br()->write("Package file builded: " . $finalPackageFileName)->br();
        
        return true;
    }
}
