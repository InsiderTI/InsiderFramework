<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods responsible for defines a registry object
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Manipulation\Registry
 */
trait Registry
{
    /**
     * Function that looks for a component in the framework component register
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Registry
     *
     * @param string $class Component class name
     *
     * @return array Returns component information in an array
     */
    public static function getComponentRegistryData(string $class): array
    {
        $regcomponentfile = \Modules\InsiderFramework\Core\Json::getJSONDataFile(
            INSTALL_DIR . DIRECTORY_SEPARATOR .
            'framework' . DIRECTORY_SEPARATOR .
            'registry' . DIRECTORY_SEPARATOR .
            'components.json'
        );

        if ($regcomponentfile === false) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister('Cannot load components.json data');
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
     * @package Modules\InsiderFramework\Core\Manipulation\Registry
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
     * @package Modules\InsiderFramework\Core\Manipulation\Registry
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
     * @package Modules\InsiderFramework\Core\Manipulation\Registry
     *
     * @param string $itemsearch Name of the item
     * @param string $section    App Section Name
     * @param string $info       Specifies which data will be returned
     *
     * @return array|string Item information
     */
    public static function getItemInfo(string $itemsearch = null, string $section = null, string $info = null)
    {
        if ($info !== null) {
            $info = strtolower($info);
        }

        $filespath = [];
        $registryDir = INSTALL_DIR . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'registry';

        if ($section !== null) {
            switch (strtolower($section)) {
                case 'guild':
                    $filespath[] = $registryDir . DIRECTORY_SEPARATOR .
                                   'guilds.json';
                    break;

                case 'app':
                    $filespath[] = $registryDir . DIRECTORY_SEPARATOR .
                                   'apps.json';
                    break;

                case 'component':
                    $filespath[] = $registryDir . DIRECTORY_SEPARATOR .
                                   "components.json";
                    break;

                case 'module':
                    $filespath[] = $registryDir . DIRECTORY_SEPARATOR .
                                   "modules.json";
                    break;

                default:
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        "Registry Error: Unknown item section %" . $section . "%",
                        "app/sys"
                    );
                    break;
            }
        } else {
            $filespath['guilds'] = $registryDir . DIRECTORY_SEPARATOR .
                                   "guilds.json";

            $filespath['apps'] = $registryDir . DIRECTORY_SEPARATOR .
                                 "apps.json";

            $filespath['components'] = $registryDir . DIRECTORY_SEPARATOR .
                                       "components.json";

            $filespath['modules'] = $registryDir . DIRECTORY_SEPARATOR .
                                    "modules.json";
        }

        foreach ($filespath as $sectionFP => $filepath) {
            $regcomponentfile = \Modules\InsiderFramework\Core\Json::getJSONDataFile($filepath);

            if (is_array($regcomponentfile)) {
                foreach ($regcomponentfile as $module => $moduleData) {
                    if ($itemsearch !== null) {
                        if (strtolower($module) === strtolower($itemsearch)) {
                            if ($info !== null) {
                                if (isset($moduleData[$info])) {
                                    return $moduleData[$info];
                                } else {
                                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                        "Registry Error: The info %" . $info .
                                        "% does not exist in the module record %" . $module . "%",
                                        "app/sys"
                                    );
                                }
                            } else {
                                return $moduleData;
                            }
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
                            return ($regcomponentfile);
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

        return array('version' => '0.0.0');
    }

    /**
     * Search for the version of dependency on a app, guild, component, etc.
     *
     * @todo Create update logic and dependencies
     * @todo This function is not used by the system for now...
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Registry
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
                    "framework" . DIRECTORY_SEPARATOR .
                    "registry" . DIRECTORY_SEPARATOR .
                    "controls" . DIRECTORY_SEPARATOR .
                    $obj . DIRECTORY_SEPARATOR .
                    $objVersion . DIRECTORY_SEPARATOR .
                    "control.json";
        
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
}
