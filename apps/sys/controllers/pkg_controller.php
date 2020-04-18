<?php

namespace Controllers\sys;

/**
 * Classe com as funções de gerenciamento de modules
 *
 * @author Marcello Costa
 *
 * @package Controllers\sys\PkgController
 *
 * @Route(path="/sys")
 */
class PkgController extends \Modules\InsiderFramework\Core\Controller
{
    /** @var string Diretório de modules do mirror */
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
        $localAuthorization = \Modules\InsiderFramework\Core\Manipulation\Registry::getLocalAuthorization(REQUESTED_URL);

        // Requisição local
        // Se o token de autorização é válido
        if ($authorization === $localAuthorization) {
            $dataReturn = \Modules\InsiderFramework\Core\Manipulation\Registry::getItemInfo($item);

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
     * Função que retorna a versão formatada de um module.
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\PkgController
     *
     * @param array $dataInfoItem Dados de um único module
     *
     * @return string|bool Versão do module
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
            case 'module':
                $filePath = $registryDirectory . DIRECTORY_SEPARATOR . 
                            "modules.json";
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
            case 'object':
                $dataArray = array(
                    "version" => $version,
                    "directory" => $directory
                );
                break;
        }
        
        // Atualizando/criando registro
        $jsonData[$item] = $dataArray;
        return \Modules\InsiderFramework\Core\Json::setJSONDataFile($jsonData, $filePath, true);
    }
    
    
    /**
     * Faz o download de um module em algum dos mirros configurados
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\PkgController
     *
     * @param string $module Nome do module
     *
     * @return string Caminho do arquivo baixado
    */
    public function downloadModule($module): string
    {
        $climate = \Modules\InsiderFramework\Core\KernelSpace::getVariable('climate', 'insiderFrameworkSystem');

        // Array de modules encontrados
        $foundModules = [];

        // Buscando qual a versão do module instalada locamente (se instalado)
        $authorization = \Modules\InsiderFramework\Core\Manipulation\Registry::getLocalAuthorization(REQUESTED_URL);
        $localVersion = json_decode($this->getInstalledItemInfo($module, $authorization, false));

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
                $path = $parsedDomain['path'];
            
                $post = array(
                    'item' => $module,
                    'path' => array(
                        $path => $repo['AUTHORIZATION']
                    )
                );

                $repoData[$domain] = $post;
            } else {
                $repoData[$domain]['path'][$path] = $repo['AUTHORIZATION'];
            }
        }

        // Para cada domínio
        foreach ($repoData as $domain => $domainData) {
            // Para cada path
            foreach ($domainData['path'] as $path => $authorization) {
                $url = $domain . "/sys/existsinmirror";

                // Requisitando mirror
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                    'item' =>  $module,
                    'path' => $path,
                    'authorization' => $authorization
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
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
                } else {
                    // Pegando o código HTTP de retorno
                    $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    if ($resultStatus == 200) {
                        if ($content !== null) {
                            $data = json_decode($content);

                            // Se retornou a versão, o module existe no mirror
                            if (is_object($data) && (property_exists($data, 'version'))) {
                                // Incluindo module no array
                                $remoteVersion = $data->version;
                                $foundModules[$domain] = array(
                                    'version' => $remoteVersion,
                                    'authorization' => $authorization,
                                    'path' => $path
                                );
                            } else {
                                $msg = "Request to server failed with content: '" . $content;
                                $climate->br();
                                $climate->to('error')->red($msg)->br();
                                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
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
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
                    }
                }
                curl_close($ch);
            }
        }

        // Quando terminar a busca, para cada module encontrado, verifica
        // em qual servidor está o module mais novo
        $latestVersion = "";
        $latestServer = "";
        foreach ($foundModules as $server => $data) {
            $version = $data['version'];

            $remoteVersion = \Modules\InsiderFramework\Core\Manipulation\Registry::getVersionParts($version);
            if ($remoteVersion === false) {
                $msg = "Wrong module version on remote server ($module): $version";
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

            // Se é 0.0.0 o module não está instalado ou se a versão do servidor é maior que a versão local
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

        // Se encontrou algum servidor com o module atualizado
        if (filter_var($latestServer, FILTER_VALIDATE_URL) !== false) {
            $moduleDir = PkgController::$mirrorDir . $foundModules[$latestServer]['path'];
            if (!is_dir($moduleDir)) {
                \Modules\InsiderFramework\Core\FileTree::createDirectory($moduleDir, 777);
            }
            
            $fileDestPath = $moduleDir . DIRECTORY_SEPARATOR . $module . '-' . $remoteVersion . '.ifm';

            // Se o arquivo existe, apaga o mesmo
            if (file_exists($fileDestPath)) {
                \Modules\InsiderFramework\Core\FileTree::deleteFile($fileDestPath);
            }

            // Baixando arquivo
            set_time_limit(0);

            $fp = fopen($fileDestPath, 'w+');
            if (is_bool($fp)) {
                $msg = "Cannot open module file %$fileDestPath%";
                $climate->br();
                $climate->to('error')->red($i10nMsg)->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $post = array(
                'module' => $module,
                'path' => $foundModules[$latestServer]['path'],
                'authorization' => $foundModules[$latestServer]['authorization'],
                'version' => $remoteVersion
            );

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
            curl_setopt($ch, CURLOPT_URL, $latestServer . "/sys/servemodule");
            curl_setopt($ch, CURLOPT_USERAGENT, 'Framework_Internal_UserAgent');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $ifmcontent = curl_exec($ch);
            
            if ($ifmcontent[0] == "<" || $ifmcontent[0] == "{") {
                $msg = "Error downloading module " . $ifmcontent;
                $climate->br();
                $climate->to('error')->red($msg)->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }

            $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fwrite($fp, $ifmcontent);
            fclose($fp);

            // Se o arquivo ainda não existe, erro
            if (!file_exists($fileDestPath)) {
                $msg = "Cannot create module on mirror directory ";
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
     * Verifica se o module está disponivel no mirror.
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
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'path')
        ) {
            $error = true;
        }
        if (
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'authorization')
        ) {
            $error = true;
        }
        if ($error) {
            $msg = 'Invalid request parameters';
            $errorCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['CriticalError'];
            http_response_code($errorCode['responsecode']);
            error_log($msg);
            $this->responseJson($msg);
        }

        $path = $POST['path'];
        $item = $POST['item'];
        $authorization = $POST['authorization'];

        // Chave de autorização local do framework
        $localAuthorization = \Modules\InsiderFramework\Core\Manipulation\Registry::getLocalAuthorization(REQUESTED_URL . $path);

        // Se o token de autorização é inválido
        if ($authorization !== $localAuthorization) {
            if ($localAuthorization."" === ""){
                $msg = 'Received null Authorization Token';
            } else {
                $msg = 'Invalid Authorization Token: '.$authorization;
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

        // Verificando se o module existe no cache de mirror
        // Se o diretório de mirror não existe
        if (!is_dir(PkgController::$mirrorDir)) {
            \Modules\InsiderFramework\Core\FileTree::createDirectory(PkgController::$mirrorDir, 777);
            $msg = 'Is is not a valid repository';
            $errorCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['CriticalError'];
            http_response_code($errorCode);
            error_log($msg);
            $this->responseJson($msg);
            die();
        }

        $fileName = PkgController::$mirrorDir . $path . DIRECTORY_SEPARATOR . $item . "-*.ifm";

        $list = glob($fileName);

        // Se o(s) arquivo(s) existe(m) no cache
        if (count($list) !== 0) {
            $latestModule = "";
            foreach ($list as $file) {
                unset($fileVersion);
                $startIndexFile = strpos($file, "-");
                if (isset($file[$startIndexFile + 1])) {
                    $fileVersion = substr($file, $startIndexFile + 1);
                }
                if (isset($fileVersion) && $fileVersion !== "") {
                    $latestModule = basename($fileVersion, '.ifm');
                }
            }

            $this->responseJson(array(
                "version" => $latestModule
            ));
            return;
        }

        // Module not found
        $msg = "Module '" . $item . "' not found";
        $notFoundCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'routingActions',
            'RoutingSystem'
        )['NotFound'];
        http_response_code($notFoundCode['responsecode']);
        $this->responseJson($msg);
        error_log($msg);
    }

    /**
     * Fornece o download de um module requisitado via url
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\PkgController
     *
     * @Route(path="servemodule")
     * 
     * @return void
     */
    public function servemodule(): void
    {   
        $POST = \Modules\InsiderFramework\Core\KernelSpace::getVariable('POST', 'insiderFrameworkSystem');
        
        if (
            !is_array($POST) ||
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'authorization') ||
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'version') ||
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'path') ||
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'module')
        ) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister('Missing parameters on request');
        }
        
        // Chave de autorização local do framework
        $path = $POST['path'];
        $localAuthorization = \Modules\InsiderFramework\Core\Manipulation\Registry::getLocalAuthorization(REQUESTED_URL . $path);
        $authorization = $POST['authorization'];
        $version = $POST['version'];
        $module = $POST['module'];

        // Requisição local
        // Se o token de autorização é inválido
        if ($authorization !== $localAuthorization) {
            if ($localAuthorization."" === ""){
                $msg = 'Received null Authorization Token';
            } else {
                $msg = 'Invalid Authorization Token: '.$authorization;
            }
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister($msg);
        }
        
        // Se o diretório de mirror não existe
        if (!is_dir(PkgController::$mirrorDir)) {
            \Modules\InsiderFramework\Core\FileTree::createDirectory(PkgController::$mirrorDir, 777);
        }

        // Verificando se o module existe no cache de mirror
        $pathOfModule = PkgController::$mirrorDir . $path . DIRECTORY_SEPARATOR . $module . "-" . $version . ".ifm";
        if (!file_exists($pathOfModule)) {
            $i10nMsg = \Modules\InsiderFramework\Core\Manipulation\I10n::getTranslate(
                "The %" . $module . "% has not found in mirror",
                "app/sys"
            );
            $this->responseJson($i10nMsg, 404);
        } else {
            $this->serveFile($pathOfModule, basename($pathOfModule));
        }
    }
}
