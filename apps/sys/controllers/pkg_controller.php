<?php

namespace Controllers\sys;

/**
 * Classe com as funções de gerenciamento de packages
 *
 * @author Marcello Costa
 *
 * @package Controllers\sys\PkgController
 *
 * @Route(path="/sys")
 */
class PkgController extends \Modules\InsiderFramework\Core\Controller
{
    /** @var string Diretório de packages do mirror */
    public static $mirrorDir = INSTALL_DIR . DIRECTORY_SEPARATOR . "mirror";

    /**
     * Teste climate
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\PkgController
     *
     * @Route (path="testeclimate")
     *
     * @return void
    */
    public function testeClimate(): void
    {
        echo "teste concluído";
    }
    
    /**
     * Função que retorna as informações de um item instalado
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\PkgController
     *
     * @Route (path="getinstallediteminfo")
     *
     * @param string $item          Item que está sendo buscado
     * @param string $authorization Token de autorização
     * @param bool   $requestCall   Flag que determina se a função está
     *                              sendo chamada via request ou não
     *
     * @return string|void Dados do item
    */
    public function getInstalledItemInfo(
        string $item = null,
        string $authorization = null,
        bool $requestCall = false
    ): ?string
    {
        $POST = \Modules\InsiderFramework\Core\KernelSpace::getVariable('POST', 'insiderFrameworkSystem');
        
        if ($item === null || $authorization === null) {
            if (!is_array($POST)) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError('Wrong request body');
            }
            if (\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'item')) {
                $item = $POST['item'];
            }
            if (\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'authorization')) {
                $authorization = $POST['authorization'];
            }

            if ($item === null || $authorization === null) {
                if (!$requestCall) {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister(
                        'Invalid arguments for sys/getinstallediteminfo route'
                    );
                } else {
                    return 'Invalid arguments for sys/getinstallediteminfo route';
                }
            }
        }

        // Chave de autorização local do framework
        $localAuthorization = \Modules\InsiderFramework\Core\Registry::getLocalAuthorization(REQUESTED_URL);

        // Requisição local
        // Se o token de autorização é válido
        if ($authorization === $localAuthorization) {
            $dataReturn = \Modules\InsiderFramework\Core\Registry::getItemInfo($item);

            if (!$requestCall) {
                return json_encode($dataReturn);
            } else {
                $this->responseJson($dataReturn);
            }
        } else {
            if ($authorization."" !== "") {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister('Invalid Authorization Token: '.$authorization);
            } else {
                if (!$requestCall) {
                    return json_encode('Received null Authorization Token');
                } else {
                    $this->responseJson('Received null Authorization Token');
                }
            }
        }
    }
    
    /**
     * Função que retorna a versão formatada de um package.
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\PkgController
     *
     * @param array $dataInfoItem Dados de um único package
     *
     * @return string|bool Versão do package
    */
    public function getVersionFromInfo(array $dataInfoItem)
    {
        if (
            isset($dataInfoItem['part1']) &&
            isset($dataInfoItem['part2']) &&
            isset($dataInfoItem['part3'])
        ) {
            return $dataInfoItem['part1'] . "." . $dataInfoItem['part2'] . "." . $dataInfoItem['part3'];
        }
        return false;
    }
    
    /**
     * Registra um item no registro do framework
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\PkgController
     *
     * @param string $section   Section of item
     * @param string $item      Name of item
     * @param string $version   Version of item
     * @param string $directory Directory of item
     *
     * @return bool Retorno da operação
    */
    public function registerItem($section, $item, $version, $directory = null): bool
    {
        $item = strtolower($item);
        $registryDirectory = INSTALL_DIR . DIRECTORY_SEPARATOR .
                             "framework" . DIRECTORY_SEPARATOR .
                             "registry";

        switch ($section) {
            case 'guild':
                $filePath = $registryDirectory . DIRECTORY_SEPARATOR .
                "guilds.json";
                break;
            case 'app':
                $filePath = $registryDirectory . DIRECTORY_SEPARATOR . 
                            "apps.json";
                break;
            default:
                \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister('Invalid Section');
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
        switch ($section) {
            case 'guild':
            case 'app':
            case 'module':
                $dataArray = array(
                    "version" => $version
                );
                break;
        }
        
        // Atualizando/criando registro
        $jsonData[$item] = $dataArray;
        return \Modules\InsiderFramework\Core\Json::setJSONDataFile($jsonData, $filePath, true);
    }
    
    
    /**
     * Faz o download de um package em algum dos mirros configurados
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\PkgController
     *
     * @param string $package Nome do package
     *
     * @return string Caminho do arquivo baixado
    */
    public function downloadPackage(string $package): string
    {
        $climate = \Modules\InsiderFramework\Core\KernelSpace::getVariable('climate', 'insiderFrameworkSystem');

        // Array de packages encontrados
        $foundPackages = [];

        // Buscando qual a versão do package instalada locamente (se instalado)
        $localAuthorization = \Modules\InsiderFramework\Core\Registry::getLocalAuthorization(REQUESTED_URL);

        if ($localAuthorization === false){
            $noAuthCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['NotAuth'];

            $msg = "Client Error - Cannot retrive local authorization for download package ".$localAuthorization;
            http_response_code($noAuthCode['responsecode']);
            error_log($msg);
            $this->responseJson($msg);
            die();
        }

        $localVersion = json_decode($this->getInstalledItemInfo($package, $localAuthorization, false));

        // Variável que guarda todos os repositórios mapeados
        $repoData = [];
        
        if (count(REMOTE_REPOSITORIES) === 0) {
            return "false";
        }
        
        $domain = "";
        // Para cada repositório configurado
        foreach (REMOTE_REPOSITORIES as $repo) {
            if ($domain === "") {
                $parsedDomain = parse_url($repo['DOMAIN']);
                $domain = $parsedDomain['scheme'] . "://" . $parsedDomain['host'];
            }
            
            if (!isset($repoData[$domain])) {
                $post = array(
                    'item' => $package,
                    'authorization' => $repo['AUTHORIZATION']
                );

                $repoData[$domain] = $post;
            } else {
                $repoData[$domain] = $repo['AUTHORIZATION'];
            }
        }

        // Para cada domínio
        foreach ($repoData as $domain => $domainData) {
            $url = $domain . "/sys/existsinmirror";

            // Requisitando mirror
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'item' =>  $package,
                'authorization' => $localAuthorization
            ));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Framework_Internal_UserAgent');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $content = curl_exec($ch);

            // Se não conseguiu alcançar o servidor
            if (curl_errno($ch)) {
                $msg = "Could not send request to server. ERROR: " . curl_error($ch);
                $climate->br();
                $climate->to('error')->red($msg)->br();
                continue;
            } else {
                // Pegando o código HTTP de retorno
                $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($resultStatus == 200) {
                    if ($content !== null) {
                        $data = json_decode($content);

                        // Se retornou a versão, o package existe no mirror
                        if (is_object($data) && (property_exists($data, 'version'))) {
                            // Incluindo package no array
                            $remoteVersion = $data->version;
                            $foundPackages[$domain] = array(
                                'version' => $remoteVersion,
                                'authorization' => $localAuthorization
                            );
                        } else {
                            $msg = "Request to server failed with content: '" . $content;
                            $climate->br();
                            $climate->to('error')->red($msg)->br();
                            continue;
                        }
                    }
                } else {
                    $addMessError = "";
                    if (curl_error($ch) !== "" && curl_error($ch) !== null) {
                        $addMessError = " Details: " . curl_error($ch);
                    } else {
                        $addMessError = " Details: " . $content;
                    }
                    $msg = "Request to server failed with status '" . $resultStatus . "'." . $addMessError;
                    $climate->br();
                    $climate->to('error')->red($msg)->br();
                    continue;
                }
            }
            curl_close($ch);
        }

        // Quando terminar a busca, para cada package encontrado, verifica
        // em qual servidor está o package mais novo
        $latestVersion = "";
        $latestServer = "";
        foreach ($foundPackages as $server => $data) {
            $version = $data['version'];

            $remoteVersion = \Modules\InsiderFramework\Core\Registry::getVersionParts($version);
            if ($remoteVersion === false) {
                $msg = "Wrong package version on remote server ($package): $version";
                $climate->br();
                $climate->to('error')->red($msg)->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }

            $remoteVersion = $this->getVersionFromInfo($remoteVersion);

            // Se o objeto da versão local não é válido
            if (!is_object($localVersion) || (!property_exists($localVersion, 'version'))) {
                $msg = "Invalid local version registry: " . json_encode($localVersion);
                $climate->br();
                $climate->to('error')->red($msg)->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }

            // Se é 0.0.0 o package não está instalado ou se a versão do servidor é maior que a versão local
            // Se não está instalado
            if ($localVersion->version === "0.0.0.") {
                if (version_compare($remoteVersion, $localVersion->version) > 0) {
                    $latestVersion = $remoteVersion;
                    $latestServer = $server;
                }
            } else {
                if (version_compare($remoteVersion, $localVersion->version) <= 0) {
                    $latestVersion = "up-to-date";
                } else {
                    $latestVersion = $remoteVersion;
                    $latestServer = $server;
                }
            }
        }

        // Se encontrou algum servidor com o package atualizado
        if (filter_var($latestServer, FILTER_VALIDATE_URL) !== false) {
            if (!is_dir(PkgController::$mirrorDir)) {
                \Modules\InsiderFramework\Core\FileTree::createDirectory(PkgController::$mirrorDir, 777);
            }
            
            $fileDestPath = PkgController::$mirrorDir . DIRECTORY_SEPARATOR . $package . '-' . $remoteVersion . '.pkg';

            // Se o arquivo existe, apaga o mesmo
            if (file_exists($fileDestPath)) {
                \Modules\InsiderFramework\Core\FileTree::deleteFile($fileDestPath);
            }

            // Baixando arquivo
            set_time_limit(0);

            $fp = fopen($fileDestPath, 'w+');
            if (is_bool($fp)) {
                $msg = "Cannot open package file %$fileDestPath%";
                $climate->br();
                $climate->to('error')->red($i10nMsg)->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $post = array(
                'package' => $package,
                'authorization' => $foundPackages[$latestServer]['authorization'],
                'version' => $remoteVersion
            );

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
            curl_setopt($ch, CURLOPT_URL, $latestServer . "/sys/servepackage");
            curl_setopt($ch, CURLOPT_USERAGENT, 'Framework_Internal_UserAgent');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $pkgcontent = curl_exec($ch);
            
            if ($pkgcontent[0] == "<" || $pkgcontent[0] == "{") {
                $msg = "Error downloading package " . $pkgcontent;
                $climate->br();
                $climate->to('error')->red($msg)->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }

            $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fwrite($fp, $pkgcontent);
            fclose($fp);

            // Se o arquivo ainda não existe, erro
            if (!file_exists($fileDestPath)) {
                $msg = "Cannot create package on mirror directory ";
                $climate->br();
                $climate->to('error')->red($msg)->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }
            
            return $fileDestPath;
        } else {
            if ($latestVersion === "up-to-date") {
                return $latestVersion;
            } else {
                return "false";
            }
        }
    }
    
    /**
     * Verifica se o package está disponivel no mirror.
     *
     * @todo É claro que isto pode ter um cache de ambos os lados,
     * mas por enquanto ficará implementado sem esta funcionalidade.
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\PkgController
     *
     * @Route(path="existsinmirror")
     * 
     * @return void
     */
    public function existsInMirror(): void
    {
        $POST = \Modules\InsiderFramework\Core\KernelSpace::getVariable('POST', 'insiderFrameworkSystem');

        $error = false;
        if (
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'item')
        ) {
            $error = true;
        }
        if (
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'authorization')
        ) {
            $error = true;
        }
        
        if ($error) {
            $msg = 'Server Error - Invalid request parameters';
            $errorCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['CriticalError'];
            http_response_code($errorCode['responsecode']);
            error_log($msg);
            $this->responseJson($msg);
        }

        $item = $POST['item'];
        $authorization = $POST['authorization'];

        // Chave de autorização local do framework
        $domainForAuthorization = REQUESTED_URL;
        $localAuthorization = \Modules\InsiderFramework\Core\Registry::getLocalAuthorization($domainForAuthorization);

        if ($localAuthorization === false){
            $noAuthCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['NotAuth'];

            $msg = "Server Error - Cannot retrive local authorization for ".$domainForAuthorization;
            http_response_code($noAuthCode['responsecode']);
            error_log($msg);
            $this->responseJson($msg);
            die();
        }

        // Se o token de autorização é inválido
        if ($authorization !== $localAuthorization) {
            if ($localAuthorization."" === ""){
                $msg = 'Server Error - Received null authorization token';
            } else {
                $msg = 'Server Error - Invalid authorization token: '.$authorization;
            }
            $noAuthCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['NotAuth'];
            http_response_code($noAuthCode['responsecode']);
            error_log($msg);
            $this->responseJson($msg);
            die();
        }

        // Verificando se o package existe no cache de mirror
        // Se o diretório de mirror não existe
        if (!is_dir(PkgController::$mirrorDir)) {
            \Modules\InsiderFramework\Core\FileTree::createDirectory(PkgController::$mirrorDir, 777);
            $msg = 'Server Error - Is is not a valid repository';
            $errorCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['CriticalError'];
            http_response_code($errorCode);
            error_log($msg);
            $this->responseJson($msg);
            die();
        }

        $fileName = PkgController::$mirrorDir . DIRECTORY_SEPARATOR . $item . "-*.pkg";

        $list = glob($fileName);

        // Se o(s) arquivo(s) existe(m) no cache
        if (count($list) !== 0) {
            $latestPackage = "";
            foreach ($list as $file) {
                unset($fileVersion);
                $startIndexFile = strpos($file, "-");
                if (isset($file[$startIndexFile + 1])) {
                    $fileVersion = substr($file, $startIndexFile + 1);
                }
                if (isset($fileVersion) && $fileVersion !== "") {
                    $latestPackage = basename($fileVersion, '.pkg');
                }
            }

            $this->responseJson(array(
                "version" => $latestPackage
            ));
            return;
        }

        // Package not found
        $msg = "Package '" . $item . "' not found";
        $notFoundCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'routingActions',
            'RoutingSystem'
        )['NotFound'];
        http_response_code($notFoundCode['responsecode']);
        $this->responseJson($msg);
        error_log($msg);
    }

    /**
     * Fornece o download de um package requisitado via url
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\PkgController
     *
     * @Route(path="servepackage")
     * 
     * @return void
     */
    public function servepackage(): void
    {   
        $POST = \Modules\InsiderFramework\Core\KernelSpace::getVariable('POST', 'insiderFrameworkSystem');
        
        if (
            !is_array($POST) ||
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'authorization') ||
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'version') ||
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'package')
        ) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister('Missing parameters on request');
        }
        
        // Chave de autorização local do framework
        $localAuthorization = \Modules\InsiderFramework\Core\Registry::getLocalAuthorization(REQUESTED_URL);
        $authorization = $POST['authorization'];
        $version = $POST['version'];
        $package = $POST['package'];

        // Requisição local
        // Se o token de autorização é inválido
        if ($authorization !== $localAuthorization) {
            if ($localAuthorization."" === ""){
                $msg = 'Server Error - Received null Authorization Token';
            } else {
                $msg = 'Server Error - Invalid Authorization Token: '.$authorization;
            }
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister($msg);
        }
        
        // Se o diretório de mirror não existe
        if (!is_dir(PkgController::$mirrorDir)) {
            \Modules\InsiderFramework\Core\FileTree::createDirectory(PkgController::$mirrorDir, 777);
        }

        // Verificando se o package existe no cache de mirror
        $pathOfPackage = PkgController::$mirrorDir . DIRECTORY_SEPARATOR . $package . "-" . $version . ".pkg";
        if (!file_exists($pathOfPackage)) {
            $i10nMsg = \Modules\InsiderFramework\Core\Manipulation\I10n::getTranslate(
                "The %" . $package . "% has not found in mirror",
                "app/sys"
            );
            $this->responseJson($i10nMsg, 404);
        } else {
            $this->serveFile($pathOfPackage, basename($pathOfPackage));
        }
    }
}
