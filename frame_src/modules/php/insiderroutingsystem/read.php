<?php

// Namespace insiderRoutingSystem
namespace Modules\insiderRoutingSystem;

/**
 * Classe de leitura do módulo de roteamento
 *
 * @author Marcello Costa
 */
class Read {
    // Patterns de declaração de rotas, actions e etc
    public static $declarationPattern = '/@(?P<declaration>.*?(?=\(|$))/';
    public static $betweenCommasPattern = '/(,)?(?P<Argument>.*?)=(?P<PreDataDelimiter>[\'"])(?P<Data>.*?)(?P<PosDataDelimiter>[\'"])/';
    public static $patternArgs = "/" . "@(?P<declaration>.*?(?=\(|$))\((?P<args>.*?.*)\)" . "/";
    public static $regexParentheses = "/\((?P<data>.*?)\)/";
    
    /**
      Função que lê todas as rotas dos controllers de
      todos os packs + arquivos JSON dos packs

      @author Marcello Costa

      @package KeyClass\Route

      @return void
     */
    public function ReadControllerRoutes() : void {
        global $kernelspace;
        $urlRoutesTmp = $kernelspace->getVariable('urlRoutesTmp', 'insiderRoutingSystem');

        if ($urlRoutesTmp === null) {
            $urlRoutesTmp = [];
        }

        // Buscando os packs registrados
        $packsRegistry = \KeyClass\JSON::getJSONDataFile(INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "registry" . DIRECTORY_SEPARATOR . "packs.json");

        // Se existem packs registrados
        if (is_array($packsRegistry)) {
            // Mapeando diretórios dos packs
            $packsMap = array();

            // Se o arquivo de rotas não existir, cria
            $routesFile = INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "routes.json";
            if (!file_exists($routesFile)){
                \KeyClass\JSON::setJSONDataFile('[]', $routesFile);
                if (!file_exists($routesFile)){
                    primaryError("Cannot create cache file for routes");
                }
            }

            // Para cada pack registrado
            foreach (array_keys($packsRegistry) as $packName) {
                // Se o pack ainda não foi mapeado
                if (!in_array($packName, $packsMap)) {
                    // Adicionando ao mapa de packs
                    $packsMap[] = $packName;

                    // Se não existir o diretório do pack
                    if (!is_dir(INSTALL_DIR . DIRECTORY_SEPARATOR . 'packs' . DIRECTORY_SEPARATOR . $packName)) {
                        \KeyClass\Error::i10nErrorRegister("The package directory %" . $packName . "% was not found", 'pack/sys');
                    }

                    // Verificando os controllers do pack
                    $controllersPack = glob('packs' . DIRECTORY_SEPARATOR . $packName . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . '*.php', GLOB_BRACE);

                    // Para cada controller dos packs
                    foreach ($controllersPack as $cp) {
                        // Mapeando as rotas do controller
                        $this->MapRoutes($cp, $packName);
                    }
                }
            }
            
            // Carregando as rotas do arquivo JSON
            $urlRoutesFromFile = \KeyClass\JSON::getJSONDataFile($routesFile);
            
            // Carregando as rotas mapeadas para uma variável
            $urlRoutesNow = $kernelspace->getVariable('urlRoutes', 'insiderRoutingSystem');
            
            // Se as rotas mapeadas agora são diferentes das que já existiam
            if ($urlRoutesFromFile === false || json_encode($urlRoutesFromFile) !== json_encode($urlRoutesNow)) {
                // Registrando novas rotas no arquivo global de rotas
                $urlRoutesFile = \KeyClass\JSON::setJSONDataFile($urlRoutesNow, $routesFile, true);
                if (!$urlRoutesFile) {
                    \KeyClass\Error::i10nErrorRegister("Error writing global route file", 'pack/sys');
                }
            }
        }
        // Se não conseguiu ler o arquivo JSON de packs
        else {
            $filePath = INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "registry" . DIRECTORY_SEPARATOR . "packs.json";
            \KeyClass\Error::i10nErrorRegister("File %" . $filePath . "% not found", 'pack/sys');
        }
    }
    
    /**
      Função que registra todas as rotas mapeadas para um controller

      @author Marcello Costa

      @package KeyClass\Route

      @param  string  $pathFileController    Caminho relativo onde está o controller
      @param  string  $packName              Nome do pack dono do controller

      @return Void
     */
    private function MapRoutes(string $pathFileController, string $packName) : void {
        global $kernelspace;
        
         // Construindo nome do arquivo de cache
        $packName = strtolower($packName);

        // Pegando o nome do controller
        $tmp = explode("/", $pathFileController);
        $controller = str_replace('_controller.php', '', strtolower($tmp[count($tmp) - 1]));
        $C = \KeyClass\Request::Controller(
                        $packName . "\\" . $controller, [], false
        );
        $rc = new \ReflectionClass(get_class($C));

        // Recuperando os comentários da classe do controller
        $classComments = $rc->getDocComment();

        $defaultAction = null;
        $routeController = null;

        // Se o controller náo tem comentários da classe, deve ser desconsiderado
        if ($classComments === false) {
            return;
        }
        
        // Buscando as definições da classe do controller
        $classDefinitions = Annotation::getAnnotationsData($rc->name, $classComments);

        // Se existir a defaultaction
        if (isset($classDefinitions[$rc->name]['route']['defaultaction'])){
            $defaultAction = $classDefinitions[$rc->name]['route']['defaultaction'];
        }
        
        // Se o controller tem uma rota definida, então ele também tem actions
        // (métodos a serem mapeados automaticamente)
        if (isset($classDefinitions[$rc->name]['route'])) {
            // Recuperando todos os métodos do controller
            $controllerMethods = $rc->getMethods(\ReflectionMethod::IS_PUBLIC);
            
            // Nome completo do controller
            $completeControllerName=strtolower("controllers\\".$packName . "\\" . $controller."_controller");

            // Para cada método encontrado dentro de um controller
            foreach ($controllerMethods as $cM) {
                // Se o método pertence ao controller que está sendo avaliado
                // (esta otimização só não irá funcionar para controller que
                // extendem de outros controllers. Se quiser habilitar
                // este caso, basta comentar este "if").
                if (strtolower($cM->class) === $completeControllerName){
                    // Recuperando comentários do método
                    $commentsMethod = $rc->getMethod($cM->name)->getDocComment();

                    // Buscando os dados das annotations do método
                    $idMethod = $completeControllerName."\\".$cM->name;
                    $annotationMethodData = Annotation::getAnnotationsData($idMethod, $commentsMethod); 
 
                    $aMD = $annotationMethodData[$idMethod];

                    // Se não existe variável de rota, não entra no mapeamento
                    if (!isset($aMD['route'])){
                        continue;
                    }

                    // Inicializando permissões da rota
                    if (!isset($aMD['permission'])){
                        $aMD['permission']=[];
                    }
                    if (!isset($aMD['permission']['type'])){
                        $aMD['permission']['type']=ACL_METHOD;
                    }
                    if (!isset($aMD['permission']['users'])){
                        $aMD['permission']['users']='';
                    }
                    if (!isset($aMD['permission']['groups'])){
                        $aMD['permission']['groups']='';
                    }
                    if (!isset($aMD['permission']['rules'])){
                        $aMD['permission']['rules']='';
                    }

                    $permissions = array(
                        'permissionType' => $aMD['permission']['type'],
                        'permissionCustomRules' => $aMD['permission']['rules'],
                        'users' => $aMD['permission']['users'],
                        'groups' => $aMD['permission']['groups']
                    );

                    if (!isset($aMD['verbs'])){
                        if (isset($classDefinitions[$rc->name]['verbs'])){
                            $aMD['verbs']=$classDefinitions[$rc->name]['verbs'];
                        }
                        else{
                            $aMD['verbs']=[];
                        }
                    }
                    $verbsRoute = $aMD['verbs'];

                    if (!isset($aMD['responseformat'])){
                        if (isset($classDefinitions[$rc->name]['responseformat'])){
                            $aMD['responseformat']=$classDefinitions[$rc->name]['responseformat'];
                        }
                        else{
                            $aMD['responseformat']=DEFAULT_RESPONSE_FORMAT;
                        }
                    }
                    $responseFormat = $aMD['responseformat'];

                    if (!isset($aMD['domains'])){
                        if (isset($classDefinitions[$rc->name]['domains'])){
                            $aMD['domains']=$classDefinitions[$rc->name]['domains'];
                        }
                        else{
                            $aMD['domains']=[];
                        }
                    }
                    $domainsRoute = $aMD['domains'];

                    if (!isset($aMD['param'])){
                        $aMD['param']=null;
                    }
                    $paramsRegexArray=$aMD['param'];

                    // Adicionando action ao array de rotas
                    if (isset($classDefinitions[$rc->name]['route']['path'])){
                        $routeController = $classDefinitions[$rc->name]['route']['path'];
                    }

                    // Se não existe o routeController, ignora o método
                    if (!isset($routeController)){
                        continue;
                    }

                    $method = $cM->name;

                    if (isset($aMD['route']['cache'])){
                        $cacheRoute = $aMD['route']['cache'];
                    }
                    else{
                        $cacheRoute = $kernelspace->getVariable('SagaciousCacheStatus', 'sagacious');
                    }
                    $path = $aMD['route']['path'];

                    $this->mapAction(
                        $packName, 
                        $controller,
                        $routeController,
                        $method,
                        $permissions, 
                        $defaultAction,
                        $path, 
                        $paramsRegexArray, 
                        $verbsRoute,
                        $responseFormat,
                        $domainsRoute,
                        $cacheRoute
                    );
                }
            }
        }
    }
        
    /**
      Adiciona a Action ao array de actions

      @author Marcello Costa

      @package KeyClass\Route

      @param  string  $packName         Pack da rota
      @param  string  $controller       Controller do método
      @param  string  $routeController  Rota ao qual a action pertence
      @param  string  $method           Nome do método da action
      @param  array   $permissions      Array de permissões da action
      @param  string  $defaultAction    Default action do controller
      @param  string  $path             Path relativo à action
      @param  array   $paramsRegexArray Array de parâmetros e seus regex para a rota
      @param  array   $verbsRoute       Array de verbos permitidos para a rota
      @param  string  $responseFormat   Define o formato de resposta de uma action
      @param  array   $domainsRoute     Array de domínios permitidos para a rota
      @param  string  $cacheRoute       Define se a rota possui alguma regra de cache

      @return void Without return
     */
    public function mapAction(string $packName, string $controller, string $routeController, string $method, array $permissions = null, string $defaultAction = null, string $path = null, array $paramsRegexArray = null, array $verbsRoute = [], string $responseFormat = "", array $domainsRoute = [], string $cacheRoute = null) : void {
        global $kernelspace;
        $urlRoutes = $kernelspace->getVariable('urlRoutes', 'insiderRoutingSystem');

        $method = strtolower($method);
        if ($defaultAction !== null) {
            $defaultAction = strtolower($defaultAction);
        }

        // Se não existirem domínios para a rota
        if (count($domainsRoute) === 0) {
            // É para qualquer domínio
            $domainsRoute[] = '*';
        }

        // Para cada domínio da rota
        foreach ($domainsRoute as $domain) {
            $domain = strtolower($domain);
            
            // Adicionando protocolo http por padrão
            if ($domain !== "*"){
                $data = parse_url($domain);
                if (!isset($data['scheme'])){
                    $domain="http://".$domain;
                }
            }

            // Montando o array
            $routeObj = array(
                $routeController => array(
                    'defaultAction' => $defaultAction,
                    'actions' => $this->createArrayPermissionAction($packName, $controller, $method, $permissions, $path, $paramsRegexArray, $verbsRoute, $responseFormat, $cacheRoute)
                )
            );

            // Se o domínio já existe
            if (isset($urlRoutes[$domain])) {
                // Se a rota já existe para o domínio
                if (isset($urlRoutes[$domain][$routeController])) {
                    // Verifica se a action que está sendo inserida também já existe (baseado no nome do método)
                    $actionExist = false;
                    if (array_key_exists($method, $urlRoutes[$domain][$routeController]['actions'])) {
                        // Se existe a chave "controller" é porque existe apenas um método $alias em um único controller
                        if (array_key_exists("controller", $urlRoutes[$domain][$routeController]['actions'][$method])) {
                            $actionExist = true;
                            $multipleControllers = false;
                        }
                        // Se não existe a chave "controller" é porque este método está sendo declarado em múltiplos controllers
                        else {
                            $actionExist = true;
                            $multipleControllers = true;
                        }
                    }
                    if ($actionExist) {
                        // Verifica se a rota que existe aceita os mesmos verbos da action que está sendo inserida
                        if (is_array($urlRoutes[$domain][$routeController]['actions'][$method]['verbs'])) {
                            $intersect = empty(array_intersect($routeObj[$routeController]['actions'][$method]['verbs'], $urlRoutes[$domain][$routeController]['actions'][$method]['verbs']));
                        } else {
                            $intersect = true;
                        }

                        // Se não existem verbos em comum nas rotas
                        if ($intersect) {
                            // Se são múltiplos controllers para um método com o mesmo nome
                            if ($multipleControllers) {
                                // Pegando o índice que existe neste momento
                                $lastActionIndex = intval(key($urlRoutes[$domain][$routeController]['actions'][$method]));

                                // Adicionando esta action ao array de actions deste método
                                $urlRoutes[$domain][$routeController]['actions'][$method][$lastActionIndex + 1] = $routeObj['actions'][$method];
                            }
                            // Se ainda não são múltiplos controllers
                            else {
                                // Pega a estrutura atual da action
                                $tmp = $urlRoutes[$domain][$routeController]['actions'][$method];

                                // Zerando o valor do array na action
                                $urlRoutes[$domain][$routeController]['actions'][$method] = [];

                                // Inserindo a antiga action novamente no array
                                $urlRoutes[$domain][$routeController]['actions'][$method][0] = $tmp;

                                // Adicionando esta action ao array de actions deste método
                                $routeObj = array_reverse($routeObj);
                                $firstElement = array_pop($routeObj);
                                $urlRoutes[$domain][$routeController]['actions'][$method][1] = $firstElement['actions'][$method];
                            }
                        }

                        // Se existe verbos em comum nas rotas
                        else {
                            // Gera um erro
                            \KeyClass\Error::i10nErrorRegister("The route %" . $routeController . "/" . $method . "% already exists in the system for the domain %" . $domain . "% (error in pack %" . $this->pack . "%)", 'pack/sys');
                        }
                    }
                    // Se a action não existe
                    else {
                        // Inserindo no array global
                        $urlRoutes[$domain] = array_merge_recursive($urlRoutes[$domain], $routeObj);
                    }
                }
                // Se a rota não existe
                else {
                    // Inserindo no array global
                    $urlRoutes[$domain] = array_merge_recursive($urlRoutes[$domain], $routeObj);
                }
            }
            // Se o domínio não existe
            else {
                // Inserindo no array global
                $urlRoutes[$domain] = $routeObj;
            }

            // Corrigindo duplicidade do nome do pack dentro da rota
            foreach ($urlRoutes[$domain][$routeController]['actions'] as $k => $action) {
                if (isset($action['pack']) && is_array($action['pack'])) {
                    $tmpPack = array_unique($action['pack']);
                    if (count($tmpPack) > 1) {
                        // Gera um erro
                        \KeyClass\Error::i10nErrorRegister("The route %" . $routeController . "/" . $method . "% has actions that are listed in more than one pack %" . json_encode(array_values($tmpPack)) . "%", 'pack/sys');
                    }
                    $urlRoutes[$domain][$routeController]['actions'][$k]['pack'] = $tmpPack[0];
                }
            }

            // Corrigindo duplicidade da defaultAction dentro da rota
            if (is_array($urlRoutes[$domain][$routeController]['defaultAction'])) {
                $tmpDA = array_unique($urlRoutes[$domain][$routeController]['defaultAction']);

                // Removendo valores nulos
                foreach ($tmpDA as $tK => $tV) {
                    if ($tV === null) {
                        unset($tmpDA[$tK]);
                    }
                }

                // Se existir mais de uma defaultAction
                if (count($tmpDA) > 1) {
                    // Gera um erro
                    \KeyClass\Error::i10nErrorRegister("The route %" . $routeController . "% has more than one defaultAction %" . json_encode(array_values($tmpDA)) . "%", 'pack/sys');
                }
                if (count($tmpDA) > 0) {
                    $urlRoutes[$domain][$routeController]['defaultAction'] = $tmpDA[array_keys($tmpDA)[0]];
                }
            }
        }

        $kernelspace->setVariable(array('urlRoutes' => $urlRoutes), 'insiderRoutingSystem');
    }
    
    /**
      Retorna uma ação com as devidas permissões montadas corretamente em um array

      @author Marcello Costa

      @package KeyClass\Route

      @param  string  $pack                Pack da action
      @param  string  $controller          Controller da action
      @param  string  $method              Nome do método que será adicionado
      @param  array   $permissions         Array de permissões da action
      @param  string  $path                Path relativo à action
      @param  array   $paramsRegexArray    Array de parâmetros e seus regex para a rota
      @param  array   $verbsRoute          Array de verbos permitidos para a rota
      @param  string  $responseFormat      Define a resposta padrão da action
      @param  string  $cacheRoute          Define se a rota possui alguma regra de cache

      @return  array  Array de permissões da action
    */
    private function createArrayPermissionAction(string $pack, string $controller, string $method, array $permissions = null, string $path = null, array $paramsRegexArray = null, array $verbsRoute = [], string $responseFormat = "", string $cacheRoute = null) : array {
        // Se não foram especificados itens no array de parâmetros
        if ($paramsRegexArray === null) {
            $paramsRegexArray = [];
        }

        // Inicializando permissões padrão
        $typeGroupPermission = "exclude";
        $groupsID = [];
        $typeUsersPermission = "exclude";
        $usersID = [];

        // Se permissões foram definidas para a action
        $permissionType = "native";
        $permissionCustomRules = "";
        if ($permissions !== null) {
            if (\Helpers\globalHelper::existAndIsNotEmpty($permissions, 'permissionType')) {
                $permissionType = $permissions['permissionType'];
            }
                    
            // Se permissões para grupos foram definidas
            if (\Helpers\globalHelper::existAndIsNotEmpty($permissions,'groups')) {
                $tmpP = trim(strtolower($permissions['groups']));
                if ($tmpP !== ''){
                    $tmpP = explode('|', $tmpP);
                    if (count($tmpP) !== 2){
                        primaryError('Wrong permissions on '.$pack.'\\'.$controller.': '.json_encode($permissions));
                    }
                    
                    $groupsID=explode(',',$tmpP[0]);
                }

                // Definindo o tipo de permissão de grupos
                $typeGroupPermission = $tmpP[1];
                switch ($typeGroupPermission){
                    case "include":
                    case "exclude":
                    break;
                    default:
                        primaryError('Wrong type permissions on '.$pack.'\\'.$controller.': '.json_encode($permissions));
                    break;
                }
            }
            
            // Se permissões para usu[arios foram definidas
            if (\Helpers\globalHelper::existAndIsNotEmpty($permissions,'users')) {
                $tmpP = trim(strtolower($permissions['users']));
                if ($tmpP !== ''){
                    $tmpP = explode('|', $tmpP);
                    if (count($tmpP) !== 2){
                        primaryError('Wrong permissions on '.$pack.'\\'.$controller.': '.json_encode($permissions));
                    }
                    
                    $usersID=explode(',',$tmpP[0]);
                }

                // Definindo o tipo de permissão de grupos
                $typeUsersPermission = $tmpP[1];
                switch ($typeUsersPermission){
                    case "include":
                    case "exclude":
                    break;
                    default:
                        primaryError('Wrong type permissions on '.$pack.'\\'.$controller.': '.json_encode($permissions));
                    break;
                }
            }
            
            // Se regras adicionais para permissões foram definidas
            if (\Helpers\globalHelper::existAndIsNotEmpty($permissions,'permissionCustomRules')) {
                $permissionCustomRules=$permissions['permissionCustomRules'];
            }
        }

        $ac = array(
            $method => array(
                'pack' => $pack,
                'controller' => $controller,
                'method' => $method,
                'cache' => $cacheRoute,
                'language' => 'PHP',
                'path' => $path,
                'verbs' => array_map('strtoupper', $verbsRoute),
                'responseFormat' => strtoupper($responseFormat),
                'paramsRegexArray' => $paramsRegexArray,
                'permissions' => array(
                    "type" => $permissionType,
                    "permissionCustomRules" => $permissionCustomRules,
                    "groups" => array(
                        "type" => $typeGroupPermission,
                        "groupsID" => implode(",", $groupsID)
                    ),
                    "users" => array(
                        "type" => $typeUsersPermission,
                        "usersID" => implode(",", $usersID)
                    )
                )
            )
        );

        return $ac;
    }
}
