<?php

namespace Modules\InsiderFramework\Core\Registry\Definition;

/**
 * Class of object used in insiderconsole
 *
 * @package \Modules\InsiderFramework\Core\Registry\Definition\PackageControlData
 *
 * @author Marcello Costa
 */
class PackageControlData
{
    private $package;
    private $section;
    private $version;
    private $authors;
    private $provides;
    private $recommends;
    private $description;

    public function setPackage(string $package): void
    {
        $this->package = $package;
    }
    public function getPackage(): string
    {
        return $this->package;
    }
    public function setSection(string $section): void
    {
        $this->section = $section;
    }
    public function getSection(): string
    {
        return $this->section;
    }
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }
    public function getVersion(): string
    {
        return $this->version;
    }
    public function setAuthors(string $authors): void
    {
        $this->authors = $authors;
    }
    public function getAuthors(): string
    {
        return $this->authors;
    }
    public function setProvides(array $provides): void
    {
        $this->provides = $provides;
    }
    public function getProvides(): array
    {
        return $this->provides;
    }
    public function setDepends(array $depends): void
    {
        $this->depends = $depends;
    }
    public function getDepends(): array
    {
        return $this->depends;
    }
    public function setRecommends(array $recommends): void
    {
        $this->recommends = $recommends;
    }
    public function getRecommends(): array
    {
        return $this->recommends;
    }
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
    * Initialize method
    *
    * @author Marcello Costa
    *
    * @package \Modules\InsiderFramework\Core\Registry\Definition\PackageControlData
    *
    * @param string $controlFilePath Control file path
    *
    * @return void
    */
    public function __construct(string $controlFilePath)
    {
        if (!file_exists($controlFilePath) || !is_readable($controlFilePath)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                'Cannot read control file ' . $controlFilePath
            );
        }

        // Trying to read the JSON file
        $jsonData = \Modules\InsiderFramework\Core\Json::getJSONDataFile($controlFilePath);
        if ($jsonData === false) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                "Invalid control file: " . $controlFilePath
            );
        }
    
        $missingInfoError = [];
        if (!isset($jsonData['package']) || trim($jsonData['package']) === "") {
            $missingInfoError[] = "Information missing at control file: package";
        }
        $this->setPackage($jsonData['package']);

        if (!isset($jsonData['section']) || trim($jsonData['section']) === "") {
            $missingInfoError[] = "Information missing at control file: section";
        }
        $this->setSection($jsonData['section']);

        if (!isset($jsonData['version']) || trim($jsonData['version']) === "") {
            $missingInfoError[] = "Information missing at control file: version";
        }
        $this->setVersion($jsonData['version']);

        if (!isset($jsonData['authors']) || trim($jsonData['authors']) === "") {
            $missingInfoError[] = "Information missing at control file: authors";
        }
        $this->setAuthors($jsonData['authors']);

        if (!isset($jsonData['provides'])) {
            $missingInfoError[] = "Information missing at control file: provides";
        }
        $this->setProvides($jsonData['provides']);

        if (!isset($jsonData['depends'])) {
            $missingInfoError[] = "Information missing at control file: depends";
        }
        $this->setDepends($jsonData['depends']);

        if (!isset($jsonData['recommends'])) {
            $missingInfoError[] = "Information missing at control file: recommends";
        }
        $this->setRecommends($jsonData['recommends']);

        if (!isset($jsonData['description']) || trim($jsonData['description']) === "") {
            $missingInfoError[] = "Information missing at control file: description";
        }
        $this->setDescription($jsonData['description']);
        
        if (count($missingInfoError) > 0) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                'Errors initializing PackageControlData: ' . implode(", ", $missingInfoError)
            );
        }
    }
}
