<?php

namespace Modules\InsiderFramework\Core\RoutingSystem;

use Modules\InsiderFramework\Core\RoutingSystem\Annotation\ReadAnnotation;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Permission as PermissionType;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Route as RouteType;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Verbs as VerbsType;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Responseformat as ResponseformatType;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Domains as DomainsType;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Param as ParamType;
use Modules\InsiderFramework\Core\ClassOperations;

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
    public static $declarationPattern = '/@(?P<declaration>([^(| ]+))/';
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
            "Framework" . DIRECTORY_SEPARATOR .
            "Registry" . DIRECTORY_SEPARATOR .
            "Sections" . DIRECTORY_SEPARATOR .
            "Apps.json"
        );

        // Se existem app registrados
        if (is_array($appRegistry)) {
            // Mapeando diretórios dos app
            $appMap = array();

            // Se o arquivo de rotas não existir, cria
            $routesFile = INSTALL_DIR . DIRECTORY_SEPARATOR .
                          "Framework" . DIRECTORY_SEPARATOR .
                          "Cache" . DIRECTORY_SEPARATOR .
                          "routes.json";

            if (!file_exists($routesFile)) {
                \Modules\InsiderFramework\Core\Json::setJSONDataFile('[]', $routesFile);
                if (!file_exists($routesFile)) {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                        "Cannot create cache file for routes"
                    );
                }
            }

            // Para cada app registrado
            foreach (array_keys($appRegistry) as $appName) {
                // Se o app ainda não foi mapeado
                if (!in_array($appName, $appMap)) {
                    // Adicionando ao mapa de app
                    $appMap[] = $appName;

                    // Se não existir o diretório do app
                    if (!is_dir(INSTALL_DIR . DIRECTORY_SEPARATOR . 'Apps' . DIRECTORY_SEPARATOR . $appName)) {
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                            "The app directory %" . $appName . "% was not found",
                            "app/sys"
                        );
                    }

                    // Verificando os controllers do app
                    $controllersApp = glob(
                        'Apps' . DIRECTORY_SEPARATOR .
                        $appName . DIRECTORY_SEPARATOR .
                        'Controllers' . DIRECTORY_SEPARATOR .
                        '*.php',
                        GLOB_BRACE
                    );

                    // Para cada controller dos app
                    foreach ($controllersApp as $controllerFilePath) {
                        // Mapeando as rotas do controller
                        $this->mapRoutes($controllerFilePath);
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
                        "Framework" . DIRECTORY_SEPARATOR .
                        "Registry" . DIRECTORY_SEPARATOR .
                        "Sections" . DIRECTORY_SEPARATOR .
                        "Apps.json";

            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "File %" . $filePath . "% not found",
                "app/sys"
            );
        }
    }

    /**
    * Get all annotations of a controller class file
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Read
    *
    * @param object $reflectionControllerObj Controller object created with reflection
    *
    * @return array Class and methods annotations
    */
    private function getAllAnnotationsOfController($reflectionControllerObj): array
    {
        $classComments = $reflectionControllerObj->getDocComment();

        if ($classComments === false) {
            return array(
                'classAnnotations' => [],
                'methodsAnnotations' => []
            );
        }

        $classAnnotationsData = ReadAnnotation::getAnnotationsData(
            $reflectionControllerObj->name,
            $classComments
        );

        if (isset($classAnnotationsData[$reflectionControllerObj->name]['route'])) {
            $controllerMethods = $reflectionControllerObj->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($controllerMethods as $controllerMethod) {
                $commentsMethod = $reflectionControllerObj->getMethod($controllerMethod->name)->getDocComment();

                $idMethod = $controllerMethod->name;

                $annotationMethodData = ReadAnnotation::getAnnotationsData(
                    $idMethod,
                    $commentsMethod
                );

                $methodsAnnotationsData[$idMethod] = $annotationMethodData[$idMethod];
            }
        }

        return array(
            'classAnnotations' => $classAnnotationsData[$reflectionControllerObj->name],
            'methodsAnnotations' => $methodsAnnotationsData
        );
    }

    /**
    * Process all controller method annotations
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Read
    *
    * @param string $appName            App da rota
    * @param string $completeClassName  Complete class name of controller
    * @param array  $annotations        Array of controller annotations
    *
    * @return void
    */
    private function processControllerMethodsAnnotations(
        string $appName,
        string $completeClassName,
        array $annotations
    ): void {
        $methodsAnnotations = $annotations['methodsAnnotations'];
        if (empty($methodsAnnotations)) {
            return;
        }

        $pathRouteForMethodsOfController = $annotations['classAnnotations']['route']['path'];
        if (isset($annotations['classAnnotations']['route']['defaultaction'])) {
            $defaultAction = $annotations['classAnnotations']['route']['defaultaction'];
        }

        foreach ($methodsAnnotations as $controllerMethodName => $controllerMethod) {
            if (!isset($controllerMethod['route'])) {
                continue;
            }

            if (!isset($controllerMethod['route']['path'])) {
                continue;
            }

            $path = $controllerMethod['route']['path'];
            $method = $controllerMethodName;

            if (isset($controllerMethod['cache'])) {
                $cacheRoute = $controllerMethod['cache'];
            } else {
                $cacheRoute = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                    'SagaciousCacheStatus',
                    'sagacious'
                );
            }

            if (isset($controllerMethod['verbs'])) {
                $verbsRoute = $controllerMethod['verbs'];
            } else {
                $verbsRoute = [];
            }
            if (isset($controllerMethod['responseFormat'])) {
                $responseFormat = $controllerMethod['responseFormat'];
            } else {
                $responseFormat = \Modules\InsiderFramework\Core\Manipulation\Response::getCurrentResponseFormat();
            }
            if (isset($controllerMethod['domains'])) {
                $domainsRoute = $controllerMethod['domainsRoute'];
            } else {
                $domainsRoute = [];
            }
                
            $namespaceExploded = explode("\\", $completeClassName);
            $controller = str_replace('Controller', '', $namespaceExploded[count($namespaceExploded) - 1]);

            $this->mapAction(
                $appName,
                $controller,
                $pathRouteForMethodsOfController,
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


    /**
     * Function that records all routes mapped to a controller
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Read
     *
     * @param string $controllerFilePath Relative path where the controller is
     *
     * @return void
     */
    private function mapRoutes(string $controllerFilePath): void
    {
        $reflectionControllerObj = ClassOperations::getReflectionControllerObjectByFilePath($controllerFilePath);
        $defaultAction = null;

        $annotations = $this->getAllAnnotationsOfController($reflectionControllerObj);

        if (empty($annotations['classAnnotations']) && empty($annotations['methodsAnnotations'])) {
            return;
        }

        $completeClassName = ClassOperations::getClassNameByFilePath($controllerFilePath);
        $appName = ClassOperations::getAppNameByClassName($completeClassName);

        if (isset($annotations['classAnnotations']['route'])) {
            $this->processControllerMethodsAnnotations(
                $appName,
                $completeClassName,
                $annotations
            );
        }
    }

    /**
     * Adiciona a Action ao array de actions
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Read
     *
     * @param string $appName                         App name
     * @param string $controller                      Controller
     * @param string $pathRouteForMethodsOfController Path for routes of controller
     * @param string $method                          Name of the action method
     * @param array  $permissions                     Permission array of the action
     * @param string $defaultAction                   Default action do controller
     * @param string $path                            Path of the action
     * @param array  $paramsRegexArray                Parameters array data
     * @param array  $verbsRoute                      Verbs array of route
     * @param string $responseFormat                  Response format of the action
     * @param array  $domainsRoute                    Domains array of the route
     * @param string $cacheRoute                      Cache data
     *
     * @return void
     */
    public function mapAction(
        string $appName,
        string $controller,
        string $pathRouteForMethodsOfController,
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
                $pathRouteForMethodsOfController => array(
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
                if (isset($urlRoutes[$domain][$pathRouteForMethodsOfController])) {
                    // Verifica se a action que está sendo inserida também já existe (baseado no nome do método)
                    $actionExist = false;
                    if (array_key_exists($method, $urlRoutes[$domain][$pathRouteForMethodsOfController]['actions'])) {
                        // Se existe a chave "controller" é porque existe apenas um método $alias em um único controller
                        if (array_key_exists("controller", $urlRoutes[$domain][$pathRouteForMethodsOfController]['actions'][$method])) {
                            $actionExist = true;
                            $multipleControllers = false;
                        } else {
                            $actionExist = true;
                            $multipleControllers = true;
                        }
                    }
                    if ($actionExist) {
                        // Verifica se a rota que existe aceita os mesmos verbos da action que está sendo inserida
                        if (is_array($urlRoutes[$domain][$pathRouteForMethodsOfController]['actions'][$method]['verbs'])) {
                            $intersect = empty(array_intersect(
                                $routeObj[$pathRouteForMethodsOfController]['actions'][$method]['verbs'],
                                $urlRoutes[$domain][$pathRouteForMethodsOfController]['actions'][$method]['verbs']
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
                                    $urlRoutes[$domain][$pathRouteForMethodsOfController]['actions'][$method]
                                ));

                                // Adicionando esta action ao array de actions deste método
                                $urlRoutes[$domain][$pathRouteForMethodsOfController]['actions']
                                [$method][$lastActionIndex + 1] = $routeObj['actions'][$method];
                            } else {
                                // Pega a estrutura atual da action
                                $tmp = $urlRoutes[$domain][$pathRouteForMethodsOfController]['actions'][$method];

                                // Zerando o valor do array na action
                                $urlRoutes[$domain][$pathRouteForMethodsOfController]['actions'][$method] = [];

                                // Inserindo a antiga action novamente no array
                                $urlRoutes[$domain][$pathRouteForMethodsOfController]['actions'][$method][0] = $tmp;

                                // Adicionando esta action ao array de actions deste método
                                $routeObj = array_reverse($routeObj);
                                $firstElement = array_pop($routeObj);
                                $urlRoutes[$domain][$pathRouteForMethodsOfController]['actions']
                                [$method][1] = $firstElement['actions'][$method];
                            }
                        } else {
                            // Gera um erro
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                "The route %" . $pathRouteForMethodsOfController . "/" . $method . "% " .
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
            foreach ($urlRoutes[$domain][$pathRouteForMethodsOfController]['actions'] as $k => $action) {
                if (isset($action['app']) && is_array($action['app'])) {
                    $tmpApp = array_unique($action['app']);
                    if (count($tmpApp) > 1) {
                        // Gera um erro
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                            "The route %" . $pathRouteForMethodsOfController . "/" . $method . "% " .
                            "has actions that are listed in more than one app " .
                            "%" . json_encode(array_values($tmpPack)) . "%",
                            "app/sys"
                        );
                    }
                    $urlRoutes[$domain][$pathRouteForMethodsOfController]['actions'][$k]['app'] = $tmpPack[0];
                }
            }

            // Corrigindo duplicidade da defaultAction dentro da rota
            if (is_array($urlRoutes[$domain][$pathRouteForMethodsOfController]['defaultAction'])) {
                $tmpDA = array_unique($urlRoutes[$domain][$pathRouteForMethodsOfController]['defaultAction']);

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
                        "The route %" . $pathRouteForMethodsOfController . "% " .
                        "has more than one defaultAction " .
                        "%" . json_encode(array_values($tmpDA)) . "%",
                        "app/sys"
                    );
                }
                if (count($tmpDA) > 0) {
                    $urlRoutes[$domain][$pathRouteForMethodsOfController]['defaultAction'] = $tmpDA[array_keys($tmpDA)[0]];
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

        // Se permissões foram definidas para a action
        $permissionEngine = ACL_DEFAULT_ENGINE;
        $permissionRules = "";
        $permissionName = "";
        if ($permissions !== null) {
            if (
                \Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty(
                    $permissions,
                    'permissionEngine'
                )
            ) {
                $permissionEngine = $permissions['permissionEngine'];
            }
            if (
                    \Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty(
                        $permissions,
                        'rules'
                    )
            ) {
                $permissionRules = $permissions['rules'];
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
                    "engine" => $permissionEngine,
                    "rules" => $permissionRules
                )
            )
        );

        return $ac;
    }
}
