<?php

namespace Modules\Insiderframework\Core\Registry\Lib;

class ModuleCollection {
    private $modules;

    public function addModule(string $module, string $version){
        $this->modules[$module] = $version;
    }

    public function listModules(): array {
        return $this->modules;
    }
}

class ModuleInfo {
    private $package;
    private $section;
    private $version;
    private $authors;
    private $provides;
    private $depends;
    private $recommends;
    private $description;

    function __construct(){
        $this->provides = new ModuleCollection();
        $this->depends = new ModuleCollection();
        $this->recommends = new ModuleCollection();
    }

    public function loadFromJson(string $controlFilePath): void {
        if (!file_exists($controlFilePath) || !is_readable($controlFilePath)) {
            \Modules\Insiderframework\Core\Error::errorRegister(
                'Cannot read control file ' . $controlFilePath
            );
        }

        $jsonData = \Modules\InsiderFramework\Core\Json::getJSONDataFile($controlFilePath);
        if ($jsonData === false) {
            \Modules\Insiderframework\Core\Error::errorRegister(
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
        foreach ($jsonData['provides'] as $provideKey => $provideValue){
            $this->addProvides($provideKey, $provideValue);
        }

        if (!isset($jsonData['depends'])) {
            $missingInfoError[] = "Information missing at control file: depends";
        }
        foreach ($jsonData['depends'] as $dependsKey => $dependsValue){
            $this->addDepends($dependsKey, $dependsValue);
        }

        if (!isset($jsonData['recommends'])) {
            $missingInfoError[] = "Information missing at control file: recommends";
        }
        foreach ($jsonData['recommends'] as $recommendsKey => $recommendsValue){
            $this->addRecommends($recommendsKey, $recommendsValue);
        }

        if (!isset($jsonData['description']) || trim($jsonData['description']) === "") {
            $missingInfoError[] = "Information missing at control file: description";
        }
        $this->setDescription($jsonData['description']);

        if (count($missingInfoError) > 0) {
            \Modules\Insiderframework\Core\Error::errorRegister(
                'Errors initializing PackageControlData: ' . implode(", ", $missingInfoError)
            );
        }
    }

    public function getPackage(): string {
        return $this->package;
    }

    public function setPackage(string $package): void {
        $this->package = $package;
    }

    public function getSection(): string {
        return $this->section;
    }

    public function setSection(string $section): void {
        $this->section = $section;
    }

    public function getVersion(): string {
        return $this->version;
    }

    public function setVersion(string $version): void {
        $this->version = $version;
    }

    public function getAuthors(): string {
        return $this->authors;
    }

    public function setAuthors(string $authors): void {
        $this->authors = $authors;
    }

    public function getProvides(): array {
        return $this->provides->listModules();
    }

    public function addProvides(string $module, string $version): void {
        $this->provides->addModule($module, $version);
    }

    public function getDepends(): array {
        return $this->depends->listModules();
    }

    public function addDepends(string $module, string $version): void {
        $this->depends->addModule($module, $version);
    }

    public function getRecommends(): array {
        return $this->recommends->listModules();
    }

    public function addRecommends(string $module, string $version): void {
        $this->recommends->addModule($module, $version);
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }
}