<?php

namespace Modules\InsiderFramework\Core\RoutingSystem;

/**
 * Class of object used in RoutingSystem\Route
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
 *
 * @author Marcello Costa
 */
class RouteData
{
    public static $regexRouteParams = "/(?P<RouteSeparator>[\\/])" .
                                      "(?P<PreDataDelimiter>[\{])(?P<Data>.*?)" .
                                      "(?P<PosDataDelimiter>[\}])/";

    private $language;
    private $app;
    private $domain;
    private $route;
    private $verb;
    private $controller;
    private $cache;
    private $responseFormat;
    private $defaultAction;
    private $actionNow;
    private $paramsArray;
    private $permissions;
    private $originalUrlRequested;

    /**
     * Construct function of the class. Can receive an array and
     * with this array can set the properties of object.
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param array $properties Array to set properties of object
     *
     * @return void
     */
    public function __construct(array $properties = null)
    {
        $this->resetRouteObj($properties);
    }

    /**
     * Função que reseta o objeto de rota ao seu estado original
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param array $properties Array to set properties of object
     *
     * @return void
     */
    public function resetRouteObj($properties = null): void
    {
        // Resetando diretamente todas as propriedades do objeto
        $this->language = null;
        $this->app = null;
        $this->domain = null;
        $this->route = null;
        $this->verb = null;
        $this->controller = null;
        $this->cache = null;
        $this->responseFormat = null;
        $this->defaultAction = null;
        $this->actionNow = null;
        $this->paramsArray = null;
        $this->permissions = null;
        $this->originalUrlRequested = null;

        // Setando os estados padrão do objeto
        // Estado de cache global
        $this->setCache(\Modules\InsiderFramework\Core\KernelSpace::getVariable('SagaciousCacheStatus', 'sagacious'));

        // Estado da rota
        $this->setRoute("");

        // Ação atual
        $this->setActionNow("");

        // Parâmetros convertidos
        $this->setParamsArray([]);

        // Verbo atual
        $this->setHttpVerb('GET');

        // Domínio completo da rota atual
        $this->setDomain("");

        if (is_array($properties)) {
            if (isset($properties['type'])) {
                $this->setType($properties['type']);
            }

            if (!$this->validateAllProperties()) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                    'Invalid ErrorMessage ' . json_encode($properties)
                );
            }
        }
    }

    /**
     * Get language of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return string Language of route
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * Set language of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param string $language Language with which the route was originally written
     *
     * @return void
     */
    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    /**
     * Get response format of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return string Response format
     */
    public function getResponseFormat(): string
    {
        return $this->responseFormat;
    }

    /**
     * Set response format of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param string $responseFormat Response format
     *
     * @return void
     */
    public function setResponseFormat(string $responseFormat): void
    {
        $this->responseFormat = $responseFormat;
    }

    /**
     * Get app of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return string|null Name of the app
     */
    public function getApp(): ?string
    {
        return $this->app;
    }

    /**
     * Set property of object
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param string $app App of route
     *
     * @return void
     */
    public function setApp(string $app): void
    {
        $this->app = $app;
    }

    /**
     * Get domain of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return string|null Domain of the route
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Set domain of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param string $domain Complete domain of current request
     *
     * @return void
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * Get route path
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return string|null Route path
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * Set route path
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param string $route Route path
     *
     * @return void
     */
    public function setRoute(string $route): void
    {
        if (isset($route[0]) && $route[0] === "/") {
            $route = substr($route, 1);
        }
        $this->route = $route;
    }

    /**
     * Get Http verb
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return string|null Http verb of route
     */
    public function getHttpVerb(): ?string
    {
        return $this->verb;
    }

    /**
     * Set property of object
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param string $verb Verb of route of object
     *
     * @return void
     */
    public function setHttpVerb(string $verb): void
    {
        $this->verb = strtoupper($verb);
    }

    /**
     * Get controller of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return string|null Controller name of route
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * Set controller name of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param string $controller Controller of route
     *
     * @return void
     */
    public function setController(string $controller): void
    {
        $this->controller = $controller;
    }

    /**
     * Get cache data of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return bool|float Value of current cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set cache data of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param bool|int|float $controller Controller of route
     *
     * @return void
     */
    public function setCache($cache): void
    {
        if (!is_bool($cache) && !is_float($cache) && !is_null($cache) && $cache !== 'none') {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError('Invalid option for cache: ' . $cache);
        }
        if (is_bool($cache) || is_float($cache)) {
            $this->cache = $cache;
        }
        if (is_null($cache) || $cache === "none") {
            $this->cache = false;
        }

        // Setando o status do cache globalmente
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'SagaciousCacheStatus' => $this->cache
            ),
            'sagacious'
        );
    }

    /**
     * Get default action of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return string|null Default action of route
     */
    public function getDefaultAction(): ?string
    {
        return $this->defaultAction;
    }

    /**
     * Set default action of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param string $defaultAction Default action of route
     *
     * @return void
     */
    public function setDefaultAction(string $defaultAction): void
    {
        $this->defaultAction = $defaultAction;
    }

    /**
     * Get default action of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return string|null Action now
     */
    public function getActionNow(): ?string
    {
        return $this->actionNow;
    }

    /**
     * Set current action
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param string $actionNow Current action
     *
     * @return void
     */
    public function setActionNow(string $actionNow): void
    {
        $this->actionNow = $actionNow;
    }

    /**
     * Get params of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return array Parameters of route
     */
    public function getParamsArray(): array
    {
        return $this->paramsArray;
    }

    /**
     * Set params of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param array $paramsArray Parameters specified with the request
     *
     * @return void
     */
    public function setParamsArray(array $paramsArray): void
    {
        $this->paramsArray = $paramsArray;
    }

    /**
     * Get permissions of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return array Permissions of route
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Set permissions of route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param array $permissions Permissions of route
     *
     * @return void
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * Set original url requested
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param array $permissions Permissions of route
     *
     * @return void
     */
    public function setOriginalUrlRequested(string $originalUrlRequested): void
    {
        $this->originalUrlRequested = $originalUrlRequested;
    }

    /**
     * Get original url requested
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return string Original URL who requested the route
     */
    public function getOriginalUrlRequested(): string
    {
        return $this->originalUrlRequested;
    }

    /**
     * Function to validate properties of the class
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return bool Return of validation
     */
    public function validateAllProperties(): bool
    {
        if (
            trim($this->getFatal()) === "" ||
            trim($this->getFile()) === "" ||
            trim($this->getLine()) === "" ||
            (trim($this->getMessage()) === "" && trim($this->getText()) === "") ||
            trim($this->getSubject()) === "" ||
            trim($this->getType()) === ""
        ) {
            return false;
        }
        return true;
    }

    /**
     * Valida o path de uma rota (comparando regex, argumentos e etc)
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param string $routeRequested Rota requisitada
     * @param string $targetAction   Ação que está sendo buscada
     *
     * @return array Array de rota
     */
    public function matchRoutePath(string $routeRequested, string $targetAction): array
    {
        $urlRoutes = \Modules\InsiderFramework\Core\KernelSpace::getVariable('urlRoutes', 'RoutingSystem');

        // Definindo qual é o domínio de acesso atual
        $domainNow = \Modules\InsiderFramework\Core\RoutingSystem\Request::getCurrentDomainOfRequest();

        // Variável que irá guardar a rota que deu match
        $routeMatched = [];

        // Se existem ações específicas para este domínio
        if (isset($urlRoutes[$domainNow])) {
            $this->matchRoutePathAux($routeMatched, $routeRequested, $domainNow, $targetAction);
        }

        // Se ainda não encontrou a rota
        if (empty($routeMatched)) {
            // Tenta buscar a rota nas rotas globais (sem domínio)
            if (isset($urlRoutes["*"])) {
                $this->matchRoutePathAux($routeMatched, $routeRequested, "*", $targetAction);
            }
        }

        return $routeMatched;
    }

    /**
     * Função auxiliar da função matchRoutePath
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @param null   $routeMatched   Rota que coincidir (será um array
     *                               quando terminar o processamento)
     * @param string $routeRequested Rota requisitada
     * @param string $domain         Domínio da requisição
     * @param string $targetAction   Ação que está sendo buscada
     *
     * @return void
     */
    private function matchRoutePathAux(
        &$routeMatched,
        string $routeRequested,
        string $domain,
        string $targetAction
    ): void {
        
        $urlRoutes = \Modules\InsiderFramework\Core\KernelSpace::getVariable('urlRoutes', 'RoutingSystem');

        $actions  = [];

        // Se existir a home
        if (isset($urlRoutes[$domain]['/'])) {
            $actions = $urlRoutes[$domain]["/"]['actions'];
        }

        // Se existir a rota específica
        if (isset($urlRoutes[$domain][$routeRequested])) {
            $actions = array_merge($urlRoutes[$domain][$routeRequested]['actions'], $actions);
        }

        // Para cada ação possível
        foreach ($actions as $method => $actionV) {
            // Inicializando array de parâmetros
            $actionV['paramsArray'] = [];

            $originalPath = $actionV['path'];
            $path = str_replace("/", "\\/", $actionV['path']);

            // Se existem parâmetros para a rota
            if (strpos($path, "{") !== false) {
                $countParams = 0;

                // Substituindo na rota os parâmetros
                $path = preg_replace_callback(
                    RouteData::$regexRouteParams,
                    function ($rP) use ($actionV, $path, &$countParams) {
                        $countParams++;

                        $searchedParam = $rP['Data'];

                        // Flag que marca se o parâmetro é opcional ou não
                        $optional = false;

                        // Se é um parâmetro opcional
                        if (strpos($searchedParam, "?") !== false) {
                            // Corrigindo o nome do parâmetro
                            $searchedParam = str_replace("?", "", $searchedParam);
                            $optional = true;
                        }

                        // Se o parâmetro só existe na rota, mas não existe
                        // a especificação/regex do parâmetro
                        if (!array_key_exists($searchedParam, $actionV['paramsRegexArray'])) {
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                                "Error on declaration of route (regex not found for '" .
                                $searchedParam . " / paramsRegexArray: " .
                                json_encode($actionV['paramsRegexArray']) . ")': " .
                                $path
                            );
                        }

                        // Se é um parâmetro opcional
                        if ($optional) {
                            return "(\/(?P<" . $searchedParam . ">" .
                            $actionV['paramsRegexArray'][$searchedParam] .
                            "))?";
                        } else {
                            return "/(?P<" . $searchedParam . ">" .
                            $actionV['paramsRegexArray'][$searchedParam] . ")";
                        }
                    },
                    $path
                );
            }
            $path = "/" . $path . "/";

            $routingSettings = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingSettings',
                'RoutingSystem'
            );

            // Se o roteamento não está como case sensitive
            if (!$routingSettings['routeCaseSensitive']) {
                $targetAction = strtolower($targetAction);
            }

            // Validando a regex ao mesmo tempo que executa o preg_match_all
            $matchesString = [];
            if (preg_match_all($path, $targetAction, $matchesString, PREG_SET_ORDER) === false) {
                // Limpando o último erro (para não cair no tratamento
                // de erros do framework diretamente)
                error_clear_last();

                // Identificando o app, controller e método com erro
                $id = $actionV['app'] . "/" . $actionV['controller'] . "controller/" . $actionV['method'];

                // Disparando o erro
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError('Invalid regex on: ' . $id);
            }

            // Se encontrou a ação
            if (!empty($matchesString)) {
                // Para cada parâmetro substitui pelo seu valor
                foreach ($matchesString as $pK => $pV) {
                    foreach ($pV as $mSK => $mSV) {
                        if (is_string($mSK)) {
                            $actionV['paramsArray'][$mSK] = $mSV;
                        }
                    }
                }

                $routeMatched = $actionV;

                break;
            }
        }
    }

    /**
     * Esta é uma função para preencher os dados da rota no objeto.
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return void
     */
    public function fillRouteData(): void
    {
        $urlRoutes = \Modules\InsiderFramework\Core\KernelSpace::getVariable('urlRoutes', 'RoutingSystem');
        $server = \Modules\InsiderFramework\Core\KernelSpace::getVariable('SERVER', 'insiderFrameworkSystem');

        // Verifico se existe algum rota que coincide com
        // a rota detectada
        $domain = $this->getDomain();
        $route = "/" . $this->getRoute();
        if (isset($urlRoutes[$domain][$route])) {
            $actionNow = $this->getActionNow();

            // Se não tem actionNow
            if ($actionNow === "") {
                // A action será a default action
                if (isset($urlRoutes[$domain][$route]['defaultAction'])) {
                    $this->setActionNow($urlRoutes[$domain][$route]['defaultAction']);
                    $actionNow = $this->getActionNow();
                } else {
                    return;
                }
            }

            // Verificando se este uma rota e action que satisfazem a requisição atual
            $actionData = $this->matchRoutePath($route, $actionNow);

            // Se a rota coincide
            if (!empty($actionData)) {
                // Array de domínios que aceitam o protocolo atual
                $acceptedDomains = [];

                // Verificando qual é o protocolo exigido pela rota
                $domainProtocol = parse_url($domain, PHP_URL_SCHEME);

                // Se não foi especificado
                if ($domainProtocol === null) {
                    // Então por padrão é http
                    $domainProtocol = "http";
                }

                // ResponseFormat
                if ($actionData['responseFormat'] !== "") {
                    $this->setResponseFormat($actionData['responseFormat']);
                } else {
                    $this->setResponseFormat(DEFAULT_RESPONSE_FORMAT);
                }

                // Se o protocolo é aceito pela rota
                if (isset($server['REQUEST_SCHEME'])) {
                    if (strtolower($domainProtocol) === trim(strtolower($server['REQUEST_SCHEME']))) {
                        // Verificando se o domínio especificado pela rota é compatível com o request atual
                        $urlRouteFile = parse_url($domain)['path'];
                        $urlRequest = $server['HTTP_HOST'];

                        // Se é aceito
                        if ($urlRouteFile === null || strtolower($urlRouteFile) === strtolower($urlRequest)) {
                            // Adiciona no array de domínios aceitos no momento
                            $acceptedDomains[] = $domain;
                        }
                    }
                } else {
                    // Adiciona no array de domínios aceitos no momento
                    $acceptedDomains[] = $domain;
                }

                // Se existem domínios aceitos com o protocolo requisitado
                if (
                    count($acceptedDomains) > 0 ||
                    (!isset($actionData['domains']) ||
                    count($actionData['domains']) === 0)
                ) {
                    // Se o verbo atual é aceito na rota
                    if (
                        count($actionData["verbs"]) === 0 ||
                        (count($actionData["verbs"]) > 0 && in_array($this->getHttpVerb(), $actionData["verbs"]))
                    ) {
                        $this->setApp($actionData['app']);
                        $this->setCache((bool) $actionData['cache']);
                        $this->setDefaultAction($urlRoutes[$domain][$route]['defaultAction']);
                        $this->setActionNow($actionData['method']);
                        $this->setController($actionData['controller']);
                        $this->setPermissions($actionData['permissions']);
                        $this->setLanguage($actionData['language']);
                        $this->setParamsArray($actionData['paramsArray']);
                    }
                }
            }
        }
    }

    /**
     * Executa a rota que está setada no objeto
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return void
     */
    public function executeRoute(): void
    {
        if ($this->getApp() === null) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("No route specified on object RouteData");
        }

        // Guardando a rota atual no kernelspace
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array(
            'routeObject' => $this
        ), 'RoutingSystem');

        // Instanciando controller
        $completeNamespace = "\\Apps\\" .
                             $this->getApp() .
                             "\\Controllers\\" .
                             ucwords($this->getController() .
                             "Controller");

        $C = new $completeNamespace();

        // Definindo o tipo de response atual
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'responseFormat' => $this->getResponseFormat()
            ),
            'insiderFrameworkSystem'
        );

        // Executando ação
        call_user_func_array(array($C, $this->getActionNow()), $this->getParamsArray());
    }

    /**
     * Função que checa se o usuário tem permissão para executar a rota.
     * Se não tem, modifica o próprio objeto para rota de não autorizado.
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\RouteData
     *
     * @return void
     */
    public function validatePermissionsRoute(): void
    {
        // Inicializando variáveis de permissão e pegando variável
        // global de rotas
        
        $urlRoutes = \Modules\InsiderFramework\Core\KernelSpace::getVariable('urlRoutes', 'insiderFrameworkSystem');

        // Guardando a rota atual do kernelspace
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array(
            'routeObject' => $this
        ), 'RoutingSystem');

        // Permissões da rota atual
        $permissionsOfRoute = $this->getPermissions();

        // Verificando as permissões
        // Se a rota está com o tipo native de permissão e não
        // tem nada configurado
        if (
            $permissionsOfRoute['type'] === 'native' &&
            $permissionsOfRoute['groups']['groupsID'] === '' &&
            $permissionsOfRoute['users']['usersID'] === ''
        ) {
            // Acesso garantido
            $access = true;
        } else {
            // Verificando as permissões atuais do usuário
            $permissionNow = \Modules\InsiderFramework\Core\Manipulation\Acl::getUserAccessLevel($this);

            // Buscando no array global de rotas:
            // Rota, Action e permissão
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                array(
                    'permissionNow' => $permissionNow
                ),
                'insiderFrameworkSystem'
            );

            // Verificando de acordo com o tipo de configuração
            switch (strtolower($permissionsOfRoute['type'])) {
                case "native":
                    // Flag de acesso ao usuário atual
                    $access = false;

                    \Modules\InsiderFramework\Core\RoutingSystem\Permission::validateNativeACLPermission(
                        $this,
                        $permissionNow,
                        $access
                    );
                    break;
                case "custom":
                    // Flag de acesso ao usuário atual
                    $access = false;
                    $Sec_security = new \Apps\Sys\Controllers\SecurityController();

                    // Verificando a permissão de acesso à rota com método customizado
                    $Sec_security->validateCustomAclPermission($this, $permissionNow, $access);
                    break;
                default:
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        'Permission type of route not recognized in %' . $this->getApp() .
                        "\\" . $this->getController() . "\\" . $this->getActionNow() . ": " .
                        $permissionsOfRoute['type'],
                        "app/sys"
                    );
                    break;
            }
        }

        // Se o usuário não tem acesso
        if ($access === false) {
            $defaultActions = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'defaultActions',
                'RoutingSystem'
            );

            // Usuário não autorizado a executar a rota
            $objectRoute = \Modules\InsiderFramework\Core\RoutingSystem\Request::searchAndFillRouteData(
                $defaultActions['NotAuth']['route'],
                $defaultActions['NotAuth']['method'],
                \Modules\InsiderFramework\Core\RoutingSystem\Request::getCurrentDomainOfRequest() .
                $defaultActions['NotAuth']['route'] . "/" .
                $defaultActions['NotAuth']['method'],
                $this->getDomain()
            );

            // Copiando todas as propriedades do objeto retornado para o atual
            foreach (get_object_vars($objectRoute) as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
