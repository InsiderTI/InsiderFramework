<?php
/**
  KeyClass\Registry
*/

namespace KeyClass;

/**
   KeyClass that defines a registry object
  
   @package KeyClass\Registry
   @author Marcello Costa
*/
class Registry{
    /**
        Function that looks for a component in the framework component register
     
        @author Marcello Costa
      
        @package KeyClass\Registry
     
        @param  string  $id      Component ID in pack component registry
        @param  string  $class   Component class name
        @param  string  $pack    Pack declaring component in view
     
        @return  array  Returns component information in an array or false if not found
    */
    public static function getComponentRegistryData(string $id=null, string $class=null, string $pack=null) : array {
        if ($id === null && $class === null) {
            return false;
        }

        else {
            if ($id !== null) {
                if ($pack !== null) {
                    $regcomponentfile=\KeyClass\JSON::getJSONDataFile(INSTALL_DIR.DIRECTORY_SEPARATOR.'packs'.DIRECTORY_SEPARATOR.$pack.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'states.json');

                    if (is_array($regcomponentfile)) {
                        if (isset($regcomponentfile[$id])) {
                            return ($regcomponentfile[$id]);
                        }

                        return false;
                    }

                    else {
                        return false;
                    }
                }

                else {
                    return false;
                }
            }

            else if ($class !== null) {
                $regcomponentfile=\KeyClass\JSON::getJSONDataFile(INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'components.json');

                if (is_array($regcomponentfile)) {
                    if (isset($regcomponentfile[$class])) {
                        return ($regcomponentfile[$class]);
                    }

                    return false;
                }

                else {
                    return false;
                }
            }
        }
    }
    
    /**
        Retrieves the framework local repository authorization key
    
        @author Marcello Costa
    
        @package Core
      
        @param  string  $domain     Domain containing authorization
  
        @return  bool|string  Authorization Key
    */
    public static function getLocalAuthorization(string $domain) : string {
        if (!isset(LOCAL_REPOSITORIES[$domain]) || !isset(LOCAL_REPOSITORIES[$domain]['AUTHORIZATION'])){
            return false;
        }

        return LOCAL_REPOSITORIES[$domain]['AUTHORIZATION'];
    }
    
    /**
        Returns each version part

        @author Marcello Costa

        @package Core

        @param  string  $version    Version

        @return  array  Parts of the version
    */
    public static function getVersionParts(string $version) : array {
        $regexVersion="/(?P<part1>([0-9]*))(?P<separator1>.)(?P<part2>([0-9]*))(?P<separator2>.)(?P<part3>([0-9]*))((?P<separator3>-)(?P<part4>.*))?/";
        $versionData = [];
        preg_match_all($regexVersion, $version, $versionMatches, PREG_SET_ORDER);
        
        if (count($versionMatches) == 0){
            return false;
        }
        
        $part1 = intval($versionMatches[0]['part1']);
        $part2 = intval($versionMatches[0]['part2']);
        $part3 = intval($versionMatches[0]['part3']);
        $part4 = null;
        if (isset($versionMatches[0]['part4'])){
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
        Retrieves installation information for an item in
        record. If no item name is entered,
        returns information for all items in that section.
        If no info is entered, returns all information of the item.

        @author Marcello Costa

        @package KeyClass\Registry

        @param  string  $itemsearch        Name of the item
        @param  string  $section           Pack Section Name
        @param  string  $info              Specifies which data will be returned

        @return array|string  Item information
    */
    public static function getItemInfo(string $itemsearch=null, string $section=null, string $info=null) : array {
        if ($info !== null) {
            $info=strtolower($info);
        }

        $filespath=[];
        if ($section !== null){
            switch (strtolower($section)) {
                case 'guild':
                    $filespath[]=INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'guilds.json';
                break;

                case 'pack':
                    $filespath[]=INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'packs.json';
                break;

                case 'component':
                    $filespath[]=INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'components.json';
                break;

                case 'module':
                    $filespath[]=INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'modules.json';
                break;

                default:
                    \KeyClass\Error::i10nErrorRegister("Registry Error: Unknown item section %".$section."%", 'pack/sys');
                break;
            }
        }
        else{
            $filespath['guilds'] = INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'guilds.json';
            $filespath['packs'] = INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'packs.json';
            $filespath['components'] = INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'components.json';
            $filespath['modules'] = INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'modules.json';
        }
        
        foreach($filespath as $sectionFP => $filepath){
            $regcomponentfile=\KeyClass\JSON::getJSONDataFile($filepath);

            if (is_array($regcomponentfile)) {
                foreach ($regcomponentfile as $package => $packageData) {
                    if ($itemsearch !== null) {
                        if (strtolower($package) === strtolower($itemsearch)) {
                            if ($info !== null) {
                                if (isset($packageData[$info])) {
                                    return $packageData[$info];
                                }
                                else {
                                    \KeyClass\Error::i10nErrorRegister("Registry Error: The info %".$info."% does not exist in the package record %".$package."%", 'pack/sys');
                                }
                            }
                            else {
                                return $packageData;
                            }
                        }
                    }

                    else {
                        if ($info !== null && $info !== "") {
                            \KeyClass\Error::i10nErrorRegister("Registry Error: Incorrect call to getItemInfo() function: INFO_VALUE = %".$info."%", 'pack/sys');
                        }

                        if (strtolower($sectionFP) === strtolower($section)){
                            return ($regcomponentfile);
                        }
                    }
                }
            }
            else {
                \KeyClass\Error::i10nErrorRegister("Registry Error: File '%".$filepath."%' not found or unable to read", 'pack/sys');
            }
        }
        
        return array('version' => '0.0.0');
    }

    /**
        Function that retrieves the defined states of a component in a specific pack.
     
        @author Marcello Costa
      
        @package KeyClass\Registry
     
        @param  string  $id               Component ID
        @param  string  $pack             Pack Name
        @param  string  $stateToSearch    Component Status
     
        @return array  Component Information
    */
    public static function getComponentViewData(string $id, string $pack, string $stateToSearch=null) : array {
        $statesjson=\KeyClass\Registry::getComponentRegistryData($id, null, $pack);

        if ($statesjson !== false) {
            if ($stateToSearch === null) {
                $stateSearched=$statesjson['states'][$statesjson['defaultstate']];
            }

            else {
                $stateSearched=$statesjson['states'][$stateToSearch];
            }

            $stateclassdjson=\KeyClass\Registry::getComponentRegistryData(null, $stateSearched['class'], $pack);

            if ($stateclassdjson !== false) {
                $componentsDefined=array(
                    'id' => $id,
                    'state' => $stateSearched,
                    'directoryClass' => $stateclassdjson['directory']
                );

                return $componentsDefined;
            }

            else {
                return false;
            }
        }

        else {
            return false;
        }
    }

    /**
        Search for the version of dependency on a pack, guild, component, etc.
     
        @todo Create update logic and dependencies
        @todo This function is not used by the system for now...
      
        @author Marcello Costa
      
        @package KeyClass\Registry
     
        @param  string  $obj           Parent Object Name
        @param  string  $objVersion    Parent Object Version
        @param  string  $dependency    Dependency Name
     
        @return float  Dependency Version
    */
    public static function getDependencyRequiredVersion(string $obj, string $objVersion, string $dependency) : float {
        $ds = DIRECTORY_SEPARATOR;

        $filepath = INSTALL_DIR.$ds."frame_src".$ds."registry".$ds."controls".$ds.$obj.$ds.$objVersion.$ds."control.json";
        if (!file_exists($filepath)) {
          \KeyClass\Error::i10nErrorRegister("Registry Error: The object %".$obj."% was not found in the system registry (path: %".$filepath."%)", 'pack/sys');
        }

        $JSON = \KeyClass\JSON::getJSONDataFile($filepath);
        if (!isset($JSON['package'])) {
          \KeyClass\Error::i10nErrorRegister("Registry Error: The registry appears corrupted. The file %".$filepath."% does not contain information", 'pack/sys');
        }
        if ($JSON['package'] !== $obj) {
          \KeyClass\Error::i10nErrorRegister("Registry Error: The registry appears corrupted. The file %".$filepath."% does not contain information about pack %".$obj."%", 'pack/sys');
        }

        if (!isset($JSON['depends'][$dependency])) {
            return false;
        }

        // Mapping the minimum version and the maximum version supported
        $regexVersions="/(?P<signal1><|>=|=|>|<=)(( ) {1,})?(?P<version1>([0-9]*\.[0-9]*\.[0-9][0-9]?))((( ) {1,})?(,)?( ) {1,}(?P<signal2><|>=|=|>|<=)((( ) {1,})?)(?P<version2>([0-9]*\.[0-9]*\.[0-9][0-9]?)))?/";
        $versionData = [];
        preg_match_all($regexVersions, $JSON['depends'][$dependency], $blockMatches, PREG_SET_ORDER);
        if (is_array($blockMatches) && count($blockMatches) > 0) {
            $versionData['signal1'] = $blockMatches[0]['signal1'];
            $versionData['version1'] = $blockMatches[0]['version1'];

            if (isset($blockMatches['signal2'])) {
                $versionData['signal2']=$blockMatches[0]['signal2'];
                $versionData['version2']=$blockMatches[0]['version2'];
            }
        }
        if (isset($versionData['version2'])) {
            \KeyClass\Error::i10nErrorRegister("Multiple versions of packages are not yet supported", 'pack/sys');
        }

        return $versionData['version1'];
    }
}
