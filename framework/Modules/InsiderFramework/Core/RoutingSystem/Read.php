<?php

namespace Modules\InsiderFramework\Core\RoutingSystem;

/**
 * Classe de leitura do módulo de roteamento
 * 
 * @author Marcello Costa
 * 
 * @package Modules\InsiderFramework\Core\RoutingSystem\Read
 */
class Read
{
    // Patterns de declaração de rotas, actions e etc
    public static $declarationPattern = '/@(?P<declaration>.*?(?=\(|$))/';
    public static $betweenCommasPattern = '/(,)?(?P<Argument>.*?)=(?P<PreDataDelimiter>[\'"])' .
                                          '(?P<Data>.*?)(?P<PosDataDelimiter>[\'"])/';
    public static $patternArgs = "/" . "@(?P<declaration>.*?(?=\(|$))\((?P<args>.*?.*)\)" . "/";
    public static $regexParentheses = "/\((?P<data>.*?)\)/";

    /**
     * Função que lê todas as rotas dos controllers de
     * todos os app + arquivos JSON dos app
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Read
     *
     * @return void
     */
    public function readControllerRoutes(): void
    {
        $urlRoutesTmp = \Modules\InsiderFramework\Core\KernelSpace::getVariable('urlRoutesTmp', 'RoutingSystem');

        if ($urlRoutesTmp === null) {
            $urlRoutesTmp = [];
        }

        // Buscando os app registrados
        $appRegistry = \Modules\InsiderFramework\Core\Json::getJSONDataFile(
            INSTALL_DIR . DIRECTORY_SEPARATOR .
            "framework" . DIRECTORY_SEPARATOR .
            "registry" . DIRECTORY_SEPARATOR .
            "apps.json"
        );

        // Se existem app registrados
        if (is_array($appRegistry)) {
            // Mapeando diretórios dos app
            $appMap = array();

            // Se o arquivo de rotas não existir, cria
            $routesFile = INSTALL_DIR . DIRECTORY_SEPARATOR .
                          "framework" . DIRECTORY_SEPARATOR .
                          "cache" . DIRECTORY_SEPARATOR .
                          "routes.json";

            if (!file_exists($routesFile)) {
                \Modules\InsiderFramework\Core\Json::setJSONDataFile('[]', $routesFile);
                if (!file_exists($routesFile)) {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("Cannot create cache file for routes");
                }
            }

            // Para cada app registrado
            foreach (array_keys($appRegistry) as $appName) {
                // Se o app ainda não foi mapeado
                if (!in_array($appName, $appMap)) {
                    // Adicionando ao mapa de app
                    $appMap[] = $appName;

                    // Se não existir o diretório do app
                    if (!is_dir(INSTALL_DIR . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . $appName)) {
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                            "The app directory %" . $appName . "% was not found",
                            "app/sys"
                        );
                    }

                    // Verificando os controllers do app
                    $controllersApp = glob(
                        'apps' . DIRECTORY_SEPARATOR .
                        $appName . DIRECTORY_SEPARATOR .
                        'controllers' . DIRECTORY_SEPARATOR .
                        '*.php',
                        GLOB_BRACE
                    );

                    // Para cada controller dos app
                    foreach ($controllersApp as $cp) {
                        // Mapeando as rotas do controller
                        $this->mapRoutes($cp, $appName);
                    }
                }
            }

            // Carregando as rotas do arquivo JSON
            $urlRoutesFromFile = \Modules\InsiderFramework\Core\Json::getJSONDataFile($routesFile);

            // Carregando as rotas mapeadas para uma variável
            $urlRoutesNow = \Modules\InsiderFramework\Core\KernelSpace::getVariable('urlRoutes', 'RoutingSystem');

            // Se as rotas mapeadas agora são diferentes das que já existiam
            if ($urlRoutesFromFile === false || json_encode($urlRoutesFromFile) !== json_encode($urlRoutesNow)) {
                // Registrando novas rotas no arquivo global de rotas
                $urlRoutesFile = \Modules\InsiderFramework\Core\Json::setJSONDataFile($urlRoutesNow, $routesFile, true);
                if (!$urlRoutesFile) {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        "Error writing global route file",
                        "app/sys"
                    );
                }
            }
        } else {
            $filePath = INSTALL_DIR . DIRECTORY_SEPARATOR .
                        "framework" . DIRECTORY_SEPARATOR .
                        "registry" . DIRECTORY_SEPARATOR .
                        "apps.json";

            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister("File %" . $filePath . "% not found", "app/sys");
        }
    }

    /**
     * Função que registra todas as rotas mapeadas para um controller
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Read
     *
     * @param string $pathFileController Caminho relativo onde está o controller
     * @param string $appName           Nome do app dono do controller
     *
     * @return void
     */
    private function mapRoutes(string $pathFileController, string $appName): void
    {
        // Construindo nome do arquivo de cache
        $appName = strtolower($appName);

        // Pegando o nome do controller
        $tmp = explode("/", $pathFileController);
        $controller = str_replace('_controller.php', '', strtolower($tmp[count($tmp) - 1]));
        $C = \Modules\InsiderFramework\Core\Loaders\CmLoader::controller(
            $appName . "\\" . $controller,
            [],
            false
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
        if (isset($classDefinitions[$rc->name]['route']['defaultaction'])) {
            $defaultAction = $classDefinitions[$rc->name]['route']['defaultaction'];
        }

        // Se o controller tem uma rota definida, então ele também tem actions
        // (métodos a serem mapeados automaticamente)
        if (isset($classDefinitions[$rc->name]['route'])) {
            // Recuperando todos os métodos do controller
            $controllerMethods = $rc->getMethods(\ReflectionMethod::IS_PUBLIC);

            // Nome completo do controller
            $completeControllerName = strtolower("controllers\\" . $appName . "\\" . $controller . "controller");

            // Para cada método encontrado dentro de um controller
            foreach ($controllerMethods as $cM) {
                // Se o método pertence ao controller que está sendo avaliado
                // (esta otimização só não irá funcionar para controller que
                // extendem de outros controllers. Se quiser habilitar
                // este caso, basta comentar este "if").
                if (strtolower($cM->class) === $completeControllerName) {
                    // Recuperando comentários do método
                    $commentsMethod = $rc->getMethod($cM->name)->getDocComment();

                    // Buscando os dados das annotations do método
                    $idMethod = $completeControllerName . "\\" . $cM->name;
                    $annotationMethodData = Annotation::getAnnotationsData($idMethod, $commentsMethod);

                    $aMD = $annotationMethodData[$idMethod];

                    // Se não existe variável de rota, não entra no mapeamento
                    if (!isset($aMD['route'])) {
                        continue;
                    }

                    // Inicializando permissões da rota
                    if (!isset($aMD['permission'])) {
                        $aMD['permission'] = [];
                    }
                    if (!isset($aMD['permission']['type'])) {
                        $aMD['permission']['type'] = ACL_METHOD;
                    }
                    if (!isset($aMD['permission']['users'])) {
                        $aMD['permission']['users'] = '';
                    }
                    if (!isset($aMD['permission']['groups'])) {
                        $aMD['permission']['groups'] = '';
                    }
                    if (!isset($aMD['permission']['rules'])) {
                        $aMD['permission']['rules'] = '';
                    }

                    $permissions = array(
                        'permissionType' => $aMD['permission']['type'],
                        'permissionCustomRules' => $aMD['permission']['rules'],
                        'users' => $aMD['permission']['users'],
                        'groups' => $aMD['permission']['groups']
                    );

                    if (!isset($aMD['verbs'])) {
                        if (isset($classDefinitions[$rc->name]['verbs'])) {
                            $aMD['verbs'] = $classDefinitions[$rc->name]['verbs'];
                        } else {
                            $aMD['verbs'] = [];
                        }
                    }
                    $verbsRoute = $aMD['verbs'];

                    if (!isset($aMD['responseformat'])) {
                        if (isset($classDefinitions[$rc->name]['responseformat'])) {
                            $aMD['responseformat'] = $classDefinitions[$rc->name]['responseformat'];
                        } else {
                            $aMD['responseformat'] = DEFAULT_RESPONSE_FORMAT;
                        }
                    }
                    $responseFormat = $aMD['responseformat'];

                    if (!isset($aMD['domains'])) {
                        if (isset($classDefinitions[$rc->name]['domains'])) {
                            $aMD['domains'] = $classDefinitions[$rc->name]['domains'];
                        } else {
                            $aMD['domains'] = [];
                        }
                    }
                    $domainsRoute = $aMD['domains'];

                    if (!isset($aMD['param'])) {
                        $aMD['param'] = null;
                    }
                    $paramsRegexArray = $aMD['param'];

                    // Adicionando action ao array de rotas
                    if (isset($classDefinitions[$rc->name]['route']['path'])) {
                        $routeController = $classDefinitions[$rc->name]['route']['path'];
                    }

                    // Se não existe o routeController, ignora o método
                    if (!isset($routeController)) {
                        continue;
                    }

                    $method = $cM->name;

                    if (isset($aMD['route']['cache'])) {
                        $cacheRoute = $aMD['route']['cache'];
                    } else {
                        $cacheRoute = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                            'SagaciousCacheStatus',
                            'sagacious'
                        );
                    }
                    $path = $aMD['route']['path'];
                    
                    $this->mapAction(
                        $appName,
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
     * Adiciona a Action ao array de actions
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Read
     *
     * @param string $appName         App da rota
     * @param string $controller       Controller do método
     * @param string $routeController  Rota ao qual a action pertence
     * @param string $method           Nome do método da action
     * @param array  $permissions      Array de permissões da action
     * @param string $defaultAction    Default action do controller
     * @param string $path             Path relativo à action
     * @param array  $paramsRegexArray Array de parâmetros e seus regex para a rota
     * @param array  $verbsRoute       Array de verbos permitidos para a rota
     * @param string $responseFormat   Define o formato de resposta de uma action
     * @param array  $domainsRoute     Array de domínios permitidos para a rota
     * @param string $cacheRoute       Define se a rota possui alguma regra de cache
     *
     * @return void
     */
    public function mapAction(
        string $appName,
        string $controller,
        string $routeController,
        string $method,
        array $permissions = null,
        string $defaultAction = null,
        string $path = null,
        array $paramsRegexArray = null,
        array $verbsRoute = [],
        string $responseFormat = "",
        array $domainsRoute = [],
        string $cacheRoute = null
    ): void {
        
        $urlRoutes = \Modules\InsiderFramework\Core\KernelSpace::getVariable('urlRoutes', 'RoutingSystem');

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
            if ($domain !== "*") {
                $data = parse_url($domain);
                if (!isset($data['scheme'])) {
                    $domain = "http://" . $domain;
                }
            }

            // Montando o array
            $routeObj = array(
                $routeController => array(
                    'defaultAction' => $defaultAction,
                    'actions' => $this->createArrayPermissionAction(
                        $appName,
                        $controller,
                        $method,
                        $permissions,
                        $path,
                        $paramsRegexArray,
                        $verbsRoute,
                        $responseFormat,
                        $cacheRoute
                    )
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
                        } else {
                            $actionExist = true;
                            $multipleControllers = true;
                        }
                    }
                    if ($actionExist) {
                        // Verifica se a rota que existe aceita os mesmos verbos da action que está sendo inserida
                        if (is_array($urlRoutes[$domain][$routeController]['actions'][$method]['verbs'])) {
                            $intersect = empty(array_intersect(
                                $routeObj[$routeController]['actions'][$method]['verbs'],
                                $urlRoutes[$domain][$routeController]['actions'][$method]['verbs']
                            ));
                        } else {
                            $intersect = true;
                        }

                        // Se não existem verbos em comum nas rotas
                        if ($intersect) {
                            // Se são múltiplos controllers para um método com o mesmo nome
                            if ($multipleControllers) {
                                // Pegando o índice que existe neste momento
                                $lastActionIndex = intval(key(
                                    $urlRoutes[$domain][$routeController]['actions'][$method]
                                ));

                                // Adicionando esta action ao array de actions deste método
                                $urlRoutes[$domain][$routeController]['actions']
                                [$method][$lastActionIndex + 1] = $routeObj['actions'][$method];
                            } else {
                                // Pega a estrutura atual da action
                                $tmp = $urlRoutes[$domain][$routeController]['actions'][$method];

                                // Zerando o valor do array na action
                                $urlRoutes[$domain][$routeController]['actions'][$method] = [];

                                // Inserindo a antiga action novamente no array
                                $urlRoutes[$domain][$routeController]['actions'][$method][0] = $tmp;

                                // Adicionando esta action ao array de actions deste método
                                $routeObj = array_reverse($routeObj);
                                $firstElement = array_pop($routeObj);
                                $urlRoutes[$domain][$routeController]['actions']
                                [$method][1] = $firstElement['actions'][$method];
                            }
                        } else {
                            // Gera um erro
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                "The route %" . $routeController . "/" . $method . "% " .
                                "already exists in the system for the domain %" . $domain . "% " .
                                "(error in app %" . $this->app . "%)",
                                "app/sys"
                            );
                        }
                    } else {
                        // Inserindo no array global
                        $urlRoutes[$domain] = array_merge_recursive($urlRoutes[$domain], $routeObj);
                    }
                } else {
                    // Inserindo no array global
                    $urlRoutes[$domain] = array_merge_recursive($urlRoutes[$domain], $routeObj);
                }
            } else {
                // Inserindo no array global
                $urlRoutes[$domain] = $routeObj;
            }

            // Corrigindo duplicidade do nome do app dentro da rota
            foreach ($urlRoutes[$domain][$routeController]['actions'] as $k => $action) {
                if (isset($action['app']) && is_array($action['app'])) {
                    $tmpApp = array_unique($action['app']);
                    if (count($tmpApp) > 1) {
                        // Gera um erro
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                            "The route %" . $routeController . "/" . $method . "% " .
                            "has actions that are listed in more than one app " .
                            "%" . json_encode(array_values($tmpPack)) . "%",
                            "app/sys"
                        );
                    }
                    $urlRoutes[$domain][$routeController]['actions'][$k]['app'] = $tmpPack[0];
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
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        "The route %" . $routeController . "% " .
                        "has more than one defaultAction " .
                        "%" . json_encode(array_values($tmpDA)) . "%",
                        "app/sys"
                    );
                }
                if (count($tmpDA) > 0) {
                    $urlRoutes[$domain][$routeController]['defaultAction'] = $tmpDA[array_keys($tmpDA)[0]];
                }
            }
        }

        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('urlRoutes' => $urlRoutes), 'RoutingSystem');
    }

    /**
     * Retorna uma ação com as devidas permissões montadas corretamente em um array
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Read
     *
     * @param string $app              App da action
     * @param string $controller       Controller da action
     * @param string $method           Nome do método que será adicionado
     * @param array  $permissions      Array de permissões da action
     * @param string $path             Path relativo à action
     * @param array  $paramsRegexArray Array de parâmetros e seus regex para a rota
     * @param array  $verbsRoute       Array de verbos permitidos para a rota
     * @param string $responseFormat   Define a resposta padrão da action
     * @param string $cacheRoute       Define se a rota possui alguma regra de cache
     *
     * @return array Array de permissões da action
     */
    private function createArrayPermissionAction(
        string $app,
        string $controller,
        string $method,
        array $permissions = null,
        string $path = null,
        array $paramsRegexArray = null,
        array $verbsRoute = [],
        string $responseFormat = "",
        string $cacheRoute = null
    ): array {
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
            if (
                \Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty(
                    $permissions,
                    'permissionType'
                )
            ) {
                $permissionType = $permissions['permissionType'];
            }

            // Se permissões para grupos foram definidas
            if (\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($permissions, 'groups')) {
                $tmpP = trim(strtolower($permissions['groups']));
                if ($tmpP !== '') {
                    $tmpP = explode('|', $tmpP);
                    if (count($tmpP) !== 2) {
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                            'Wrong permissions on ' . $app . '\\' . $controller . ': ' .
                            json_encode($permissions)
                        );
                    }

                    $groupsID = explode(',', $tmpP[0]);
                }

                // Definindo o tipo de permissão de grupos
                $typeGroupPermission = $tmpP[1];
                switch ($typeGroupPermission) {
                    case "include":
                    case "exclude":
                        break;
                    default:
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                            'Wrong type permissions on ' . $app . '\\' . $controller .
                            ': ' . json_encode($permissions)
                        );
                        break;
                }
            }

            // Se permissões para usu[arios foram definidas
            if (\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($permissions, 'users')) {
                $tmpP = trim(strtolower($permissions['users']));
                if ($tmpP !== '') {
                    $tmpP = explode('|', $tmpP);
                    if (count($tmpP) !== 2) {
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                            'Wrong permissions on ' . $app . '\\' . $controller . ': ' .
                            json_encode($permissions)
                        );
                    }

                    $usersID = explode(',', $tmpP[0]);
                }

                // Definindo o tipo de permissão de grupos
                $typeUsersPermission = $tmpP[1];
                switch ($typeUsersPermission) {
                    case "include":
                    case "exclude":
                        break;
                    default:
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                            'Wrong type permissions on ' . $app . '\\' .
                            $controller . ': ' .
                            json_encode($permissions)
                        );
                        break;
                }
            }

            // Se regras adicionais para permissões foram definidas
            if (
                    \Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty(
                        $permissions,
                        'permissionCustomRules'
                    )
            ) {
                $permissionCustomRules = $permissions['permissionCustomRules'];
            }
        }

        $ac = array(
            $method => array(
                'app' => $app,
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
