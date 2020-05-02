<?php

namespace Modules\InsiderFramework\Core\Registry\Manipulation;

/**
 * Methods responsible for defines a registry object
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Registry\Manipulation\Registry
 */
trait Registry
{
    /**
     * Function that looks for a component in the framework component register
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Registry\Manipulation\Registry
     *
     * @param string $class Component class name
     *
     * @return array Returns component information in an array
     */
    public static function getComponentRegistryData(string $class): array
    {
        $regcomponentfile = \Modules\InsiderFramework\Core\Json::getJSONDataFile(
            INSTALL_DIR . DIRECTORY_SEPARATOR .
            "Framework" . DIRECTORY_SEPARATOR .
            "Registry" . DIRECTORY_SEPARATOR .
            "Sections" . DIRECTORY_SEPARATOR .
            "SagaciousComponents.json"
        );

        if ($regcomponentfile === false) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister('Cannot load SagaciousComponents.json data');
        }

        if (is_array($regcomponentfile)) {
            if (isset($regcomponentfile[$class])) {
                return ($regcomponentfile[$class]);
            }

            return [];
        } else {
            return [];
        }
    }

    /**
     * Retrieves the framework local repository authorization key
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Registry\Manipulation\Registry
     *
     * @param string $domain Domain containing authorization
     *
     * @return bool|string Authorization Key
     */
    public static function getLocalAuthorization(string $domain)
    {
        if (!isset(LOCAL_REPOSITORIES[$domain]) || !isset(LOCAL_REPOSITORIES[$domain]['AUTHORIZATION'])) {
            return false;
        }

        return LOCAL_REPOSITORIES[$domain]['AUTHORIZATION'];
    }

    /**
     * Returns each version part
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Registry\Manipulation\Registry
     *
     * @param string $version Version
     *
     * @return array Parts of the version
     */
    public static function getVersionParts(string $version): array
    {
        $regexVersion = "/(?P<part1>([0-9]*))" .
                        "(?P<separator1>.)(?P<part2>([0-9]*))" .
                        "(?P<separator2>.)(?P<part3>([0-9]*))" .
                        "((?P<separator3>-)(?P<part4>.*))?/";

        $versionData = [];
        preg_match_all($regexVersion, $version, $versionMatches, PREG_SET_ORDER);

        if (count($versionMatches) == 0) {
            return false;
        }

        $part1 = intval($versionMatches[0]['part1']);
        $part2 = intval($versionMatches[0]['part2']);
        $part3 = intval($versionMatches[0]['part3']);
        $part4 = null;
        if (isset($versionMatches[0]['part4'])) {
            $part4 = $versionMatches[0]['part4'];
        }

        return array(
            'part1' => $part1,
            'part2' => $part2,
            'part3' => $part3,
            'part4' => $part4,
        );
    }

    /**
     * Retrieves installation information for an item in
     * record. If no item name is entered,
     * returns information for all items in that section.
     * If no info is entered, returns all information of the item.
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Registry\Manipulation\Registry
     *
     * @param string $itemsearch Name of the item
     * @param string $section    App Section Name
     * @param string $info       Specifies which data will be returned
     *
     * @return array|string Item information
     */
    public static function getItemInfo(string $itemsearch = null, string $section = null, string $info = null)
    {
        $notFoundArray = array(
            'package' => '',
            'section' => '',
            'authors' => '',
            'description' => '',
            'provides' => '',
            'depends' => '',
            'recommends' => '',
            'version' => '0.0.0'
        );

        if ($info !== null) {
            $info = strtolower($info);
        }

        $filespath = [];
        $registryDir = INSTALL_DIR . DIRECTORY_SEPARATOR . 'Framework' . DIRECTORY_SEPARATOR . 'Registry';

        $itemsearch = strtolower($itemsearch);
        if ($section !== null) {
            switch (strtolower($section)) {
                case 'module':
                    $filespath[] = $registryDir . DIRECTORY_SEPARATOR .
                                   "Sections" . DIRECTORY_SEPARATOR .
                                   "Modules.json";
                    break;
                case 'guild':
                    $filespath[] = $registryDir . DIRECTORY_SEPARATOR .
                                   "Sections" . DIRECTORY_SEPARATOR .
                                   "Guilds.json";
                    break;

                case 'app':
                    $filespath[] = $registryDir . DIRECTORY_SEPARATOR .
                                   "Sections" . DIRECTORY_SEPARATOR .
                                   "Apps.json";
                    break;

                case 'sagaciouscomponent':
                    $filespath[] = $registryDir . DIRECTORY_SEPARATOR .
                                   "Sections" . DIRECTORY_SEPARATOR .
                                   "SagaciousComponents.json";
                    break;

                default:
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        "Registry Error: Unknown item section %" . $section . "%",
                        "app/sys"
                    );
                    break;
            }
        } else {
            $filespath['modules'] = $registryDir . DIRECTORY_SEPARATOR .
                                   "Sections" . DIRECTORY_SEPARATOR .
                                   "Modules.json";

            $filespath['guilds'] = $registryDir . DIRECTORY_SEPARATOR .
                                   "Sections" . DIRECTORY_SEPARATOR .
                                   "Guilds.json";

            $filespath['Apps'] = $registryDir . DIRECTORY_SEPARATOR .
                                 "Sections" . DIRECTORY_SEPARATOR .
                                 "Apps.json";

            $filespath['sagaciouscomponents'] = $registryDir . DIRECTORY_SEPARATOR .
                                       "Sections" . DIRECTORY_SEPARATOR .
                                       "SagaciousComponents.json";
        }

        $section = "";
        $packageData = [];
        foreach ($filespath as $sectionFP => $filepath) {
            if (!file_exists($filepath) || !is_readable($filepath)){
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Registry Error: File '%" . $filepath . "%' not found or unable to read",
                    "app/sys"
                );
            }
            $regcomponentfile = \Modules\InsiderFramework\Core\Json::getJSONDataFile($filepath);

            if (is_array($regcomponentfile)) {
                foreach ($regcomponentfile as $package => $packageDataInLoop) {
                    if ($itemsearch !== null) {
                        if (strtolower($package) === strtolower($itemsearch)) {
                            $packageData[$package]['section'] = $sectionFP;
                            $packageData[$package] = array_merge($packageDataInLoop, $packageData[$package]);
                        }
                    } else {
                        if ($info !== null && $info !== "") {
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                "Registry Error: Incorrect call to getItemInfo() function: INFO_VALUE = %" .
                                $info . "%",
                                "app/sys"
                            );
                        }

                        if (strtolower($sectionFP) === strtolower($section)) {
                            $packageData[$package]['section'] = $sectionFP;
                            $packageData[$package] = array_merge($regcomponentfile, $packageData[$package]);
                            break;
                        }
                    }
                }
            } else {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Registry Error: File '%" . $filepath . "%' not found or unable to read",
                    "app/sys"
                );
            }
        }

        if (!empty($packageData)){
            $packageName = array_key_first($packageData);

            // Getting control details
            $packageControlFile = INSTALL_DIR . DIRECTORY_SEPARATOR .
                                  "Framework" . DIRECTORY_SEPARATOR .
                                  "Registry" . DIRECTORY_SEPARATOR .
                                  "Controls" . DIRECTORY_SEPARATOR .
                                  $packageName . DIRECTORY_SEPARATOR .
                                  "Control.json";

            if (!file_exists($packageControlFile)){
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Registry Error: Control file '%" . $packageControlFile . "%' not found or unable to read",
                    "app/sys"
                );
            }
            $controlJsonData = \Modules\InsiderFramework\Core\Json::getJSONDataFile($packageControlFile);
            if ($controlJsonData === false){
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Registry Error: Control file '%" . $packageControlFile . "%' not found or unable to read",
                    "app/sys"
                );
            }

            $packageData = $controlJsonData;

            // Getting md5 details
            $md5ControlFile = INSTALL_DIR . DIRECTORY_SEPARATOR .
                                  "Framework" . DIRECTORY_SEPARATOR .
                                  "Registry" . DIRECTORY_SEPARATOR .
                                  "Controls" . DIRECTORY_SEPARATOR .
                                  $packageName . DIRECTORY_SEPARATOR .
                                  "Md5sum.json";
            if (!file_exists($md5ControlFile)){
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Registry Error: Md5 file '%" . $md5ControlFile . "%' not found or unable to read",
                    "app/sys"
                );
            }

            $md5JsonData = \Modules\InsiderFramework\Core\Json::getJSONDataFile($md5ControlFile);
            if ($md5JsonData === false){
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Registry Error: Control file '%" . $md5ControlFile . "%' not found or unable to read",
                    "app/sys"
                );
            }

            $packageData = array_merge($packageData, array(
                'md5sum' => $md5JsonData
            ));

            return $packageData;
        }

        return $notFoundArray;
    }

    /**
     * Search for the version of dependency on a app, guild, component, etc.
     *
     * @todo Create update logic and dependencies
     * @todo This function is not used by the system for now...
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Registry\Manipulation\Registry
     *
     * @param string $obj        Parent Object Name
     * @param string $objVersion Parent Object Version
     * @param string $dependency Dependency Name
     *
     * @return float Dependency Version
     */
    public static function getDependencyRequiredVersion(string $obj, string $objVersion, string $dependency): float
    {
        $filepath = INSTALL_DIR . DIRECTORY_SEPARATOR .
                    "Framework" . DIRECTORY_SEPARATOR .
                    "Registry" . DIRECTORY_SEPARATOR .
                    "Controls" . DIRECTORY_SEPARATOR .
                    $obj . DIRECTORY_SEPARATOR .
                    $objVersion . DIRECTORY_SEPARATOR .
                    "Control.json";
        
        if (!file_exists($filepath)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Registry Error: The object %" . $obj . "% was not found in the system registry " .
                "(path: %" . $filepath . "%)",
                "app/sys"
            );
        }

        $JSON = \Modules\InsiderFramework\Core\Json::getJSONDataFile($filepath);
        if (!isset($JSON['package'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Registry Error: The registry appears corrupted. " .
                "The file %" . $filepath . "% does not contain information",
                "app/sys"
            );
        }
        if ($JSON['package'] !== $obj) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Registry Error: The registry appears corrupted. " .
                "The file %" . $filepath . "% does not contain information about app %" . $obj . "%",
                "app/sys"
            );
        }

        if (!isset($JSON['depends'][$dependency])) {
            return false;
        }

        // Mapping the minimum version and the maximum version supported
        $regexVersions = "/(?P<signal1><|>=|=|>|<=)(( ) {1,})?" .
                         "(?P<version1>([0-9]*\.[0-9]*\.[0-9][0-9]?))((( )" .
                         " {1,})?(,)?( ) {1,}(?P<signal2><|>=|=|>|<=)((( ) " .
                         "{1,})?)(?P<version2>([0-9]*\.[0-9]*\.[0-9][0-9]?)))?/";

        $versionData = [];
        preg_match_all($regexVersions, $JSON['depends'][$dependency], $blockMatches, PREG_SET_ORDER);
        if (is_array($blockMatches) && count($blockMatches) > 0) {
            $versionData['signal1'] = $blockMatches[0]['signal1'];
            $versionData['version1'] = $blockMatches[0]['version1'];

            if (isset($blockMatches['signal2'])) {
                $versionData['signal2'] = $blockMatches[0]['signal2'];
                $versionData['version2'] = $blockMatches[0]['version2'];
            }
        }
        if (isset($versionData['version2'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister("Multiple versions of packages are not yet supported", "app/sys");
        }

        return $versionData['version1'];
    }

    /**
    * Method description
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Registry\Manipulation\Registry
    *
    * @param string $relativePath Relative path of file
    *
    * @return array Array of data
    */
    public static function getLocalConfigurationFile($relativePath): array {
        $data = \Modules\InsiderFramework\Core\Json::getJSONDataFile(
            INSTALL_DIR . DIRECTORY_SEPARATOR .
            "Framework" . DIRECTORY_SEPARATOR .
            "Registry" . DIRECTORY_SEPARATOR .
            "Local" . DIRECTORY_SEPARATOR . 
            $relativePath,
            true
        );

        if ($data === false){
            return array();
        }

        return $data;
    }

    /**
     * Register an item in the framework registry
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Registry\Manipulation\Registry
     *
     * @param object $controlData Data
     *
     * @return bool Return of the operation
    */
    public static function registerItem($packageControlData): bool
    {
        $item = strtolower($packageControlData->getPackage());
        $registryDirectory = INSTALL_DIR . DIRECTORY_SEPARATOR .
                             "Framework" . DIRECTORY_SEPARATOR .
                             "Registry";

        switch (strtolower($packageControlData->getSection())) {
            case 'guild':
                $filePath = $registryDirectory . DIRECTORY_SEPARATOR .
                            "Sections" . DIRECTORY_SEPARATOR .
                            "Guilds.json";

            case 'modules':
                $filePath = $registryDirectory . DIRECTORY_SEPARATOR .
                            "Sections" . DIRECTORY_SEPARATOR .
                            "Modules.json";
                break;
            case 'app':
                $filePath = $registryDirectory . DIRECTORY_SEPARATOR . 
                            "Sections" . DIRECTORY_SEPARATOR .
                            "Apps.json";
                break;
            default:
                \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister('Invalid Section: '.$packageControlData->getSection());
                break;
        }
        
        if (!file_exists($filePath)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister("File not found: " . $filePath);
        }
        
        // Tentando ler o arquivo JSON
        $jsonData = \Modules\InsiderFramework\Core\Json::getJSONDataFile($filePath);
        if ($jsonData === false) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister("Cannot read control file: " . $filePath);
        }
        
        // Verificando se o item já está registrado no arquivo
        $jsonData = array_change_key_case($jsonData, CASE_LOWER);
        
        // Construindo array com as novas informações
        switch (strtolower($packageControlData->getSection())) {
            case 'guild':
            case 'app':
            case 'module':
                $dataArray = array(
                    "version" => $packageControlData->getVersion()
                );
                break;
        }
        
        // Atualizando/criando registro
        $jsonData[$item] = $dataArray;
        return \Modules\InsiderFramework\Core\Json::setJSONDataFile($jsonData, $filePath, true);
    }

    /**
     * Unregister an item in the framework registry
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Registry\Manipulation\Registry
     *
     * @param object $controlData Data
     *
     * @return bool Return of the operation
    */
    public static function unregisterItem($package, $section): bool
    {
        $item = strtolower($package);
        $registryDirectory = INSTALL_DIR . DIRECTORY_SEPARATOR .
                             "Framework" . DIRECTORY_SEPARATOR .
                             "Registry";

        switch (strtolower($section)) {
            case 'guild':
                $filePath = $registryDirectory . DIRECTORY_SEPARATOR .
                            "Sections" . DIRECTORY_SEPARATOR .
                            "Guilds.json";

            case 'modules':
                $filePath = $registryDirectory . DIRECTORY_SEPARATOR .
                            "Sections" . DIRECTORY_SEPARATOR .
                            "Modules.json";
                break;
            case 'app':
                $filePath = $registryDirectory . DIRECTORY_SEPARATOR . 
                            "Sections" . DIRECTORY_SEPARATOR .
                            "Apps.json";
                break;
            default:
                \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister('Invalid Section: '.$section);
                break;
        }
        
        if (!file_exists($filePath)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister("File not found: " . $filePath);
        }
        
        // Tentando ler o arquivo JSON
        $jsonData = \Modules\InsiderFramework\Core\Json::getJSONDataFile($filePath);
        if ($jsonData === false) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister("Cannot read registry file: " . $filePath);
        }
        
        // Verificando se o item está registrado no arquivo
        var_dump($jsonData);
        die("FILE: " . __FILE__ . "<br/>LINE: " . __LINE__);
        
        // Atualizando/criando registro
        $jsonData[$item] = $dataArray;
        return \Modules\InsiderFramework\Core\Json::setJSONDataFile($jsonData, $filePath, true);
    }
}
