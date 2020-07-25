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
     * @param object $console Modules\InsiderFramework\Console\Adapter object
     *
     * @return bool Return of operation
    */
    public static function buildPackage($console): bool
    {
        $sourceDirectory = $console->getArgument('sourceDirectory');
        if (trim($sourceDirectory) === "") {
            $input = $console->input("Build Directory:");
            $sourceDirectory = $input->prompt();
            if (trim($sourceDirectory) === "") {
                $console->br();
                $console->setOutput('error');
                $console->write("Action cancelled");
                $console->br();
                die();
            }
        }

        $packageFile = $console->arguments->get('output');
        if (trim($packageFile) === "") {
            $input = $console->input("Output package file:");
            $packageFile = $input->prompt();
            if (trim($packageFile) === "") {
                $console->br();
                $console->setOutput('error');
                $console->write("Action cancelled");
                $console->br();
                die();
            }
        }
        
        // Checking if it's an valid build directory
        $validationErrors = \Modules\InsiderFramework\Core\PackageSystem\Console\Validate::validatePackageDirectoryTree(
            $sourceDirectory
        );
        if (!empty($validationErrors)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(implode(', ', $validationErrors));
        }

        // Getting version from controle file
        $packageControlData = new \Modules\InsiderFramework\Core\Registry\Definition\PackageControlData(
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
        $packageFile .= "-" . $packageControlData->getVersion() . ".pkg";
  
        $compressedPathFile = \Modules\InsiderFramework\Core\Filetree::compressDirectoryOrFile(
            $sourceDirectory,
            "zip",
            $packageFile,
            true
        );

        $console->br()->setTextColor('blue')->write("Package file builded: " . $compressedPathFile)->br();
        
        return true;
    }
}
