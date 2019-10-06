<?php
/**
  Arquivo KeyClass\Registry
*/

// Namespace das KeyClass
namespace KeyClass;

/**
   KeyClass que define um objeto registry
  
   @package KeyClass\Registry
   @author Marcello Costa
*/
class Registry{
    /**
        Função que busca um componente no registro de componentes do framework
     
        @author Marcello Costa
      
        @package KeyClass\Registry
     
        @param  string  $id      ID do componente no registro de componentes do pack
        @param  string  $class   Nome da classe do componente
        @param  string  $pack    Pack que está declarando o componente na view
     
        @return  array  Retorna as informações do componente em um array ou false
                        se não encontrar
    */
    public static function getComponentRegistryData(string $id=null, string $class=null, string $pack=null) : array {
        // Se nada foi informado
        if ($id === null && $class === null) {
            return false;
        }

        // Se algum dos dois foi informado
        else {
            // Se a busca for por nome
            // Retorna as informações do componentes no pack
            if ($id !== null) {
                // Se o pack foi informado
                if ($pack !== null) {
                    // Lendo o arquivo de registro de componentes
                    $regcomponentfile=\KeyClass\JSON::getJSONDataFile(INSTALL_DIR.DIRECTORY_SEPARATOR.'packs'.DIRECTORY_SEPARATOR.$pack.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'states.json');

                    // Se encontrar registrado o componente
                    if (is_array($regcomponentfile)) {
                        if (isset($regcomponentfile[$id])) {
                            // Retorna as informações do componente
                            return ($regcomponentfile[$id]);
                        }

                        return false;
                    }

                    // Se não encontrar registrado o componente
                    else {
                        return false;
                    }
                }

                // É necessário ter o pack para buscar por nome
                else {
                    return false;
                }
            }

            // Se a busca for por class
            // Retorna as informações de instalação do componente
            else if ($class !== null) {
                // Lendo o arquivo de registro de componentes
                $regcomponentfile=\KeyClass\JSON::getJSONDataFile(INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'components.json');

                // Se encontrar registrado o componente
                if (is_array($regcomponentfile)) {
                    if (isset($regcomponentfile[$class])) {
                        // Retorna as informações do componente
                        return ($regcomponentfile[$class]);
                    }

                    return false;
                }

                // Se não encontrar registrado o componente
                else {
                    return false;
                }
            }
        }
    }
    
    /**
        Recupera a chave de autorização de repositório local do framework
    
        @author Marcello Costa
    
        @package Core
      
        @param  string  $domain     Domínio que contém a autorização
  
        @return  bool|string  Chave de autorização
    */
    public static function getLocalAuthorization(string $domain) : string {
        if (!isset(LOCAL_REPOSITORIES[$domain]) || !isset(LOCAL_REPOSITORIES[$domain]['AUTHORIZATION'])){
            return false;
        }

        return LOCAL_REPOSITORIES[$domain]['AUTHORIZATION'];
    }
    
    /**
        Retorna cada parte da versão

        @author Marcello Costa

        @package Core

        @param  string  $version    Versão

        @return  array  Partes da versão
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
        Recupera as informações de instalação de um item no
        registro. Caso nenhum nome de item seja informado,
        retorna as informações de todos os itens daquela section.
        Caso nenhuma info seja informada, retorna todas as infos do item.

        @author Marcello Costa

        @package KeyClass\Registry

        @param  string  $itemsearch        Nome do item
        @param  string  $section           Nome da section do pacote
        @param  string  $info              Especifica qual dado será retornado

        @return  array  Informação do package
    */
    public static function getItemInfo(string $itemsearch=null, string $section=null, string $info=null) : array {
        // Tratando nome da info
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
                    // Lendo o arquivo de registro
                    $filespath[]=INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'packs.json';
                break;

                case 'component':
                    // Lendo o arquivo de registro
                    $filespath[]=INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'registry'.DIRECTORY_SEPARATOR.'components.json';
                break;

                case 'module':
                    // Lendo o arquivo de registro
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
        
        // Lendo os arquivos JSONs
        foreach($filespath as $sectionFP => $filepath){
            $regcomponentfile=\KeyClass\JSON::getJSONDataFile($filepath);

            // Para cada item encontrado
            if (is_array($regcomponentfile)) {
                foreach ($regcomponentfile as $package => $packageData) {
                    // Se foi especificado um nome de item
                    if ($itemsearch !== null) {
                        // Se for o item que está sendo procurado
                        if (strtolower($package) === strtolower($itemsearch)) {
                            // Se foi requisitada uma info específica
                            if ($info !== null) {
                                // Se a info existe
                                if (isset($packageData[$info])) {
                                    return $packageData[$info];
                                }
                                // Se não existe esta info
                                else {
                                    \KeyClass\Error::i10nErrorRegister("Registry Error: The info %".$info."% does not exist in the package record %".$package."%", 'pack/sys');
                                }
                            }
                            // Se não foi requisitada uma info específica
                            else {
                                // Retornando todas as informações
                                return $packageData;
                            }
                        }
                    }

                    // Se não foi especificado um nome de item
                    else {
                        // Se o info foi especificado mas o item não
                        if ($info !== null && $info !== "") {
                            \KeyClass\Error::i10nErrorRegister("Registry Error: Incorrect call to getItemInfo() function: INFO_VALUE = %".$info."%", 'pack/sys');
                        }

                        // Se é essa section
                        if (strtolower($sectionFP) === strtolower($section)){
                            // Retorna o registro inteiro a section
                            return ($regcomponentfile);
                        }
                    }
                }
            }
            // Se não conseguiu ler o arquivo JSON
            else {
                \KeyClass\Error::i10nErrorRegister("Registry Error: File '%".$filepath."%' not found or unable to read", 'pack/sys');
            }
        }
        
        return array('version' => '0.0.0');
    }

    /**
        Função que recupera os estados definidos de um componente em um pack específico
     
        @author Marcello Costa
      
        @package KeyClass\Registry
     
        @param  string  $id               ID do componente
        @param  string  $pack             Nome do pack
        @param  string  $stateToSearch    Estado do componente
     
        @return array  Informações do componente
    */
    public static function getComponentViewData(string $id, string $pack, string $stateToSearch=null) : array {
        // Buscando informações do componente no arquivo JSON"
        // Utilizar os dados do arquivo JSON"
        $statesjson=\KeyClass\Registry::getComponentRegistryData($id, null, $pack);

        // Verificando se foi encontrado algo
        if ($statesjson !== false) {
            // Se não foi especificado um estado a ser recuperado
            if ($stateToSearch === null) {
                // Armazenando o estado padrão do objeto
                $stateSearched=$statesjson['states'][$statesjson['defaultstate']];
            }

            // Se for especificado um estado a ser recuperado
            else {
                // Armazenando o estado especificado do componente
                $stateSearched=$statesjson['states'][$stateToSearch];
            }

            // Pegando os dados da classe default do componente
            $stateclassdjson=\KeyClass\Registry::getComponentRegistryData(null, $stateSearched['class'], $pack);

            // Se encontrar os dados
            if ($stateclassdjson !== false) {
                // Colocando o estado default do componente no array
                $componentsDefined=array(
                    'id' => $id,
                    'state' => $stateSearched,
                    'directoryClass' => $stateclassdjson['directory']
                );

                return $componentsDefined;
            }

            // Se não encontrar os dados
            else {
                return false;
            }
        }

        // Se nada foi encontrado
        else {
            return false;
        }
    }

    /**
        Busca qual é a versão da dependência de um pack, guild, component, etc
     
        @todo Criar lógica de update e dependências
      
        @author Marcello Costa
      
        @package KeyClass\Registry
     
        @todo This function is not used by the system for now...
      
        @param  string  $obj           Nome do objeto pai
        @param  string  $objVersion    Versão do objeto pai
        @param  string  $dependency    Nome da dependência
     
        @return float  Versão da dependência
    */
    public static function getDependencyRequiredVersion(string $obj, string $objVersion, string $dependency) : float {
        $ds = DIRECTORY_SEPARATOR;

        // Arquivo de controle do objeto requisitado
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

        // Se o objeto não depende da dependência informada
        if (!isset($JSON['depends'][$dependency])) {
            return false;
        }

        // Mapeando a versão mínima e a versão máxima suportadas
        // Regex de versões
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
        // No momento ainda não é suportada mais de uma versão
        if (isset($versionData['version2'])) {
            \KeyClass\Error::i10nErrorRegister("Multiple versions of packages are not yet supported", 'pack/sys');
        }

        return $versionData['version1'];
    }
}
