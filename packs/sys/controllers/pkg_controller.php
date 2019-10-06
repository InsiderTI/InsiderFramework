<?php
/**
  Controller com funções para gerenciamento de pacotes do framework
*/

// Namespace relativo ao pack do controller
namespace Controllers\sys;

/**
  Classe com as funções de gerenciamento de pacotes

  @author Marcello Costa
  
  @package Controllers\sys\Pkg_Controller
  
  @Route(path="/sys")
 */
class Pkg_Controller extends \KeyClass\Controller{
    /** @var string Diretório de pacotes do mirror */
    public static $mirrorDir = INSTALL_DIR.DIRECTORY_SEPARATOR."mirror";

    /**
        Teste climate
     
        @author Marcello Costa

        @package Controllers\sys\Pkg_Controller
     
        @Route (path="testeclimate")
     
        @return Void
    */
    public function testeclimate() {
        echo "teste concluído";
    }
    
    /**
        Função que retorna as informações de um item instalado

        @author Marcello Costa

        @package Controllers\sys\Pkg_Controller

        @Route (path="getinstallediteminfo")

        @param String $item               Item que está sendo buscado
        @param String $authorization      Token de autorização
        @param bool   $requestCall        Flag que determina se a função está
                                          sendo chamada via request ou não

        @return float Versão do framework
    */
    public function getInstalledItemInfo(string $item = null, string $authorization = null, bool $requestCall = false) {
        global $kernelspace;
        $POST = $kernelspace->getVariable('POST', 'insiderFrameworkSystem');
        
        if ($item === null || $authorization === null){
            if (!is_array($POST)){
                primaryError('Wrong request body');
            }
            if (\Helpers\globalHelper::existAndIsNotEmpty($POST, 'item')){
                $item = $POST['item'];
            }
            if (\Helpers\globalHelper::existAndIsNotEmpty($POST, 'authorization')){
                $authorization = $POST['authorization'];
            }

            if ($item === null || $authorization === null){
                if (!$requestCall){
                    \KeyClass\Error::errorRegister('Invalid arguments for sys/getinstallediteminfo route');
                }
                else{
                    return 'Invalid arguments for sys/getinstallediteminfo route';
                }
            }
        }

        // Chave de autorização local do framework
        $localAuthorization = \KeyClass\Registry::getLocalAuthorization(REQUESTED_URL);

        // Requisição local
        // Se o token de autorização é válido
        if ($authorization === $localAuthorization) {
            $dataReturn = \KeyClass\Registry::getItemInfo($item);

            if (!$requestCall){
                return json_encode($dataReturn);
            }
            else{
                $this->responseJSON($dataReturn);
            }
        }

        // Se o token não é válido
        else {
            if ($authorization !== null) {
                \KeyClass\Error::errorRegister('Invalid Authorization Token');
            }
            else {
                if (!$requestCall){
                    return json_encode('Invalid Authorization Token');
                }
                else{
                    $this->responseJSON('Invalid Authorization Token');
                }
            }
        }
    }
    
    /**
        Função que retorna a versão formatada de um pacote.

        @author Marcello Costa

        @package Core

        @param  array  $dataInfoItem    Dados de um único package

        @return  string  Versão do pacote
    */
    public function getVersionFromInfo(array $dataInfoItem) {
        if (isset($dataInfoItem['part1']) && isset($dataInfoItem['part2']) && isset($dataInfoItem['part3'])){
            return $dataInfoItem['part1'].".".$dataInfoItem['part2'].".".$dataInfoItem['part3'];
        }
        return false;
    }
    
    /**
        Registra um item no registro do framework

        @author Marcello Costa

        @package Core

        @param  string  $item         desc
        @param  string  $version      desc
        @param  string  $directory    desc

        @return  bool  Retorno da operação
    */
    public function registerItem($section, $item, $version, $directory = null) {
        $item = strtolower($item);
        switch($section){
            case 'guild':
                $filePath = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."guilds.json";
            break;
            case 'pack':
                $filePath = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."packs.json";
            break;
            case 'object':
                $filePath = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."objects.json";
            break;
            case 'module':
                $filePath = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."registry".DIRECTORY_SEPARATOR."modules.json";
            break;
            default:
                \KeyClass\Error::errorRegister('Invalid Section');
            break;
        }
        
        if (!file_exists($filePath)) {
            \KeyClass\Error::errorRegister("File not found: ".$filePath);
        }
        
        // Tentando ler o arquivo JSON
        $jsonData = \KeyClass\JSON::getJSONDataFile($filePath);
        if ($jsonData === false) {
            \KeyClass\Error::errorRegister("Cannot read control file: ".$filePath);
        }
        
        // Verificando se o item já está registrado no arquivo
        $jsonData = array_change_key_case($jsonData, CASE_LOWER);
        
        // Construindo array com as novas informações
        switch ($section) {
            case 'guild':
            case 'pack':
            case 'module':
                $dataArray = array(
                    "version" => $version
                );
            break;
            case 'object':
                $dataArray = array(
                    "version" => $version,
                    "directory" => $directory
                );
            break;
        }
        
        // Atualizando/criando registro
        $jsonData[$item]=$dataArray;
        return \KeyClass\JSON::setJSONDataFile($jsonData, $filePath, true);
    }
    
    
    /**
        Faz o download de um pacote em algum dos mirros configurados

        @author Marcello Costa

        @package Core

        @param  string  $package    Nome do pacote

        @return  string  Caminho do arquivo baixado
    */
    public function downloadPackage ($package) {
        global $kernelspace;
        $climate = $kernelspace->getVariable('climate', 'insiderFrameworkSystem');

        // Array de pacotes encontrados
        $foundPackages = [];

        // Buscando qual a versão do pacote instalada locamente (se instalado)
        $authorization = \KeyClass\Registry::getLocalAuthorization(REQUESTED_URL);
        $localVersion = json_decode($this->getInstalledItemInfo($package, $authorization, false));

        // Variável que guarda todos os repositórios mapeados
        $repoData = [];
        
        if (count(REMOTE_REPOSITORIES) === 0){
            return "false";
        }
        
        $domain = "";
        // Para cada repositório configurado
        foreach (REMOTE_REPOSITORIES as $repo) {
            if ($domain === ""){
                $parsedDomain=parse_url($repo['DOMAIN']);
                $domain = $parsedDomain['scheme']."://".$parsedDomain['host'];
            }
            
            if (!isset($repoData[$domain])){
                $path = $parsedDomain['path'];
            
                $post = array(
                    'item' => $package,
                    'path' => array(
                        $path => $repo['AUTHORIZATION']
                    )
                );

                $repoData[$domain]=$post;
            }
            else{
                $repoData[$domain]['path'][$path]=$repo['AUTHORIZATION'];
            }
        }

        // Para cada domínio
        foreach($repoData as $domain => $domainData){
            // Para cada path
            foreach($domainData['path'] as $path => $authorization){
                $url = $domain."/sys/existsinmirror";

                // Requisitando mirror
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_POST, true); 
                curl_setopt($ch, CURLOPT_POSTFIELDS, array('item' =>  $package,'path' => $path, 'authorization' => $authorization));
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Framework_Internal_UserAgent');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $content = curl_exec($ch);

                // Se não conseguiu alcançar o servidor
                if (curl_errno($ch)) {
                    $msg = "Could not send request to server. ERROR: " . curl_error($ch);
                    $climate->br();
                    $climate->to('error')->red($msg)->br();
                    primaryError($msg);
                } else {
                    // Pegando o código HTTP de retorno
                    $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    if ($resultStatus == 200) {
                        if ($content !== NULL){
                            $data = json_decode($content);

                            // Se retornou a versão, o pacote existe no mirror
                            if (is_object($data) && (property_exists($data, 'version'))) {
                                // Incluindo pacote no array
                                $remoteVersion = $data->version;
                                $foundPackages[$domain]=array(
                                    'version' => $remoteVersion,
                                    'authorization' => $authorization,
                                    'path' => $path
                                );
                            }
                            // Falha na resposta
                            else{
                                $msg = "Request to server failed with content: '" . $content;
                                $climate->br();
                                $climate->to('error')->red($msg)->br();
                                primaryError($msg);
                            }
                        }
                    }
                    // Falha dentro do método no servidor remoto
                    else {
                        $addMessError = "";
                        if (curl_error($ch) !== "" && curl_error($ch) !== null) {
                            $addMessError = " Details: " . curl_error($ch);
                        }
                        else{
                            $addMessError = " Details: " . $content;
                        }
                        $msg = "Request to server failed with status '" . $resultStatus . "'." . $addMessError;
                        $climate->br();
                        $climate->to('error')->red($msg)->br();
                        primaryError($msg);
                    }
                }
                curl_close($ch);
            }
        }

        // Quando terminar a busca, para cada pacote encontrado, verifica
        // em qual servidor está o pacote mais novo
        $latestVersion="";
        $latestServer="";
        foreach ($foundPackages as $server => $data) {
            $version = $data['version'];

            $remoteVersion = \KeyClass\Registry::getVersionParts($version);
            if ($remoteVersion === false){
                $msg = "Wrong package version on remote server ($package): $version";
                $climate->br();
                $climate->to('error')->red($msg)->br();
                primaryError($msg);
            }

            $remoteVersion = $this->getVersionFromInfo($remoteVersion);

            // Se o objeto da versão local não é válido
            if (!is_object($localVersion) || (!property_exists($localVersion, 'version'))) {
                $msg = "Invalid local version registry: " . json_encode($localVersion);
                $climate->br();
                $climate->to('error')->red($msg)->br();
                primaryError($msg);
            }

            // Se é 0.0.0 o pacote não está instalado ou se a versão do servidor é maior que a versão local
            // Se não está instalado
            if ($localVersion->version === "0.0.0."){
                if (version_compare($remoteVersion, $localVersion->version) > 0){
                    $latestVersion=$remoteVersion;
                    $latestServer=$server;
                }
            }
            // Se está instalado
            else{
                if (version_compare($remoteVersion, $localVersion->version) <= 0){
                    $latestVersion="up-to-date";
                }
                else{
                    $latestVersion=$remoteVersion;
                    $latestServer=$server;
                }
            }
            
        }

        // Se encontrou algum servidor com o pacote atualizado
        if (filter_var($latestServer, FILTER_VALIDATE_URL) !== false) {
            $packageDir = Pkg_Controller::$mirrorDir.$foundPackages[$latestServer]['path'];
            if (!is_dir($packageDir)){
                \KeyClass\FileTree::createDirectory($packageDir, 777);
            }
            
            $fileDestPath = $packageDir.DIRECTORY_SEPARATOR.$package.'-'.$remoteVersion.'.pkg';

            // Se o arquivo existe, apaga o mesmo
            if (file_exists($fileDestPath)){
                \KeyClass\FileTree::deleteFile($fileDestPath);
            }

            // Baixando arquivo
            set_time_limit(0);

            $fp = fopen($fileDestPath, 'w+');
            if (is_bool($fp)){
                $msg = "Cannot open package file %$fileDestPath%";
                $climate->br();
                $climate->to('error')->red($i10nMsg)->br();
                primaryError($msg);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $post = array(
                'package' => $package,
                'path' => $foundPackages[$latestServer]['path'],
                'authorization' => $foundPackages[$latestServer]['authorization'],
                'version' => $remoteVersion
            );

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
            curl_setopt($ch, CURLOPT_URL, $latestServer."/sys/servepackage");
            curl_setopt($ch, CURLOPT_USERAGENT, 'Framework_Internal_UserAgent');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $pkgcontent = curl_exec($ch);
            
            if ($pkgcontent[0] == "<" || $pkgcontent[0] == "{"){
                $msg = "Error downloading package ".$pkgcontent;
                $climate->br();
                $climate->to('error')->red($msg)->br();
                primaryError($msg);
            }

            $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);            
            curl_close($ch);
            fwrite($fp, $pkgcontent);
            fclose($fp);

            // Se o arquivo ainda não existe, erro
            if (!file_exists($fileDestPath)){
                $msg = "Cannot create package on mirror directory ";
                $climate->br();
                $climate->to('error')->red($msg)->br();
                primaryError($msg);
            }
            
            return $fileDestPath;
        }
        // Se não encontrou um servidor ou se o pacote já atualizado
        else{
            if ($latestVersion === "up-to-date"){
                return $latestVersion;
            }
            else {
                return "false";
            }
        }
    }
    
    /**
        Verifica se o pacote está disponivel no mirror.
        @todo É claro que isto pode ter um cache de ambos os lados, 
        mas por enquanto ficará implementado sem esta funcionalidade.
     
        @author Marcello Costa

        @package Core
        
        @Route(path="existsinmirror")
     */
    public function existsinmirror () {
        global $kernelspace;
        $POST = $kernelspace->getVariable('POST', 'insiderFrameworkSystem');

        $error = false;
        if (
            !\Helpers\globalHelper::existAndIsNotEmpty($POST, 'item')
           ) {
            $error = true;
        }
        if (
            !\Helpers\globalHelper::existAndIsNotEmpty($POST, 'path')
           ) {
            $error = true;
        }
        if (
            !\Helpers\globalHelper::existAndIsNotEmpty($POST, 'authorization')
           ) {
            $error = true;
        }
        if ($error){
            $msg = 'Invalid request parameters';
            $errorCode = $kernelspace->getVariable('routingActions', 'insiderRoutingSystem')['CriticalError'];
            http_response_code($errorCode['responsecode']);
            error_log($msg);
            $this->responseJSON($msg);
        }

        $path = $POST['path'];
        $item = $POST['item'];
        $authorization = $POST['authorization'];

        // Chave de autorização local do framework
        $localAuthorization = \KeyClass\Registry::getLocalAuthorization(REQUESTED_URL.$path);

        // Se o token de autorização é válido
        if ($authorization !== $localAuthorization) {
            $msg = 'Invalid Authorization Token';
            $noAuthCode = $kernelspace->getVariable('routingActions', 'insiderRoutingSystem')['NotAuth'];
            http_response_code($noAuthCode['responsecode']);
            error_log($msg);
            $this->responseJSON($msg);
        }

        // Verificando se o pacote existe no cache de mirror
        // Se o diretório de mirror não existe
        if (!is_dir(Pkg_Controller::$mirrorDir)){
            \KeyClass\FileTree::createDirectory(Pkg_Controller::$mirrorDir, 777);
            $msg = 'Is is not a valid repository';
            $errorCode = $kernelspace->getVariable('routingActions', 'insiderRoutingSystem')['CriticalError'];
            http_response_code($errorCode);
            error_log($msg);
            $this->responseJSON($msg);
        }

        $fileName = Pkg_Controller::$mirrorDir.$path.DIRECTORY_SEPARATOR.$item."-*.pkg";

        $list = glob($fileName);

        // Se o(s) arquivo(s) existe(m) no cache
        if (count($list) !== 0) {
            $latestPackage = "";
            foreach ($list as $file) {
                unset($fileVersion);
                $startIndexFile = strpos($file, "-");
                if (isset($file[$startIndexFile+1])) {
                    $fileVersion = substr($file, $startIndexFile+1);
                }
                if (isset($fileVersion) && $fileVersion !== "") {
                    $latestPackage= basename($fileVersion, '.pkg');
                }
            }

            return $this->responseJSON(array(
                "version" => $latestPackage
            ));
        }

        // Package not found
        $msg = "Package '".$item."' not found";
        $notFoundCode = $kernelspace->getVariable('routingActions', 'insiderRoutingSystem')['NotFound'];
        http_response_code($notFoundCode['responsecode']);
        $this->responseJSON($msg);
        error_log($msg);
    }

    /**
        Fornece o download de um pacote requisitado via url
     
        @author Marcello Costa

        @package Core
        
        @Route(path="servepackage")
     */
    public function servepackage () {
        global $kernelspace;
        $POST = $kernelspace->getVariable('POST', 'insiderFrameworkSystem');
        
        if (
            !is_array($POST) ||
            !\Helpers\globalHelper::existAndIsNotEmpty($POST, 'authorization') ||
            !\Helpers\globalHelper::existAndIsNotEmpty($POST, 'version') ||
            !\Helpers\globalHelper::existAndIsNotEmpty($POST, 'path') ||
            !\Helpers\globalHelper::existAndIsNotEmpty($POST, 'package')
           ){
            \KeyClass\Error::errorRegister('Missing parameters on request');
        }
        
        // Chave de autorização local do framework
        $path = $POST['path'];
        $localAuthorization = \KeyClass\Registry::getLocalAuthorization(REQUESTED_URL.$path);
        $authorization = $POST['authorization'];
        $version = $POST['version'];
        $package = $POST['package'];

        // Requisição local
        // Se o token de autorização é válido
        if ($authorization !== $localAuthorization) {
            \KeyClass\Error::errorRegister('Invalid Authorization Token');
        }
        
        // Se o diretório de mirror não existe
        if (!is_dir(Pkg_Controller::$mirrorDir)) {
            \KeyClass\FileTree::createDirectory(Pkg_Controller::$mirrorDir, 777);
        }

        // Verificando se o pacote existe no cache de mirror
        $pathOfPackage = Pkg_Controller::$mirrorDir.$path.DIRECTORY_SEPARATOR.$package."-".$version.".pkg";
        if (!file_exists($pathOfPackage)){
            $i10nMsg = \KeyClass\I10n::getTranslate("The %".$package."% has not found in mirror", "pack/sys");
            $this->responseJSON($i10nMsg, 404);
        }
        else{
            $this->serveFile($pathOfPackage, basename($pathOfPackage));
        }
    }
}
?>
