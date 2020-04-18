<?php

namespace Modules\InsiderFramework\Core\RoutingSystem;

use Modules\InsiderFramework\Core\RoutingSystem\RouteData;

/**
 * Classe de requisição de rota do módulo de roteamento
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Request
 *
 * @author Marcello Costa
 */
class Request
{
    /**
     * Função que requista uma URL
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Request
     *
     * @param string $url            URL requisitada
     * @param string $specificDomain Domínio específico da requisição
     *
     * @return void
     */
    public static function requestRoute(string $url = null, string $specificDomain = null): void
    {
        if ($url === null) {
            $routeFromGet = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'GET',
                'insiderFrameworkSystem'
            );

            if (is_null($routeFromGet)) {
                $url = "/";
            } else {
                if (!isset($routeFromGet['url'])) {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        "Cannot found url element when getting route from get",
                        "app/sys"
                    );
                }
                $url = $routeFromGet['url'];
            }
        }

        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array(
            'urlRequested' => $url
        ), 'RoutingSystem');

        // Verifica a validade uma rota
        $routeAndAction = \Modules\InsiderFramework\Core\RoutingSystem\Validation::getRouteAndActionDataFromUrl($url);

        // Setando a configuração de ajaxRequest
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('ajaxrequest' => $routeAndAction['ajaxRequest']), 'RoutingSystem');

        // Busca a rota correspondente
        $routeData = \Modules\InsiderFramework\Core\RoutingSystem\Request::searchAndFillRouteData(
            $routeAndAction['routeNow'],
            $routeAndAction['actionNow'],
            $routeAndAction['originalUrlRequested'],
            $specificDomain
        );

        // Setando a configuração de responseFormat
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('responseFormat' => $routeData->getResponseFormat()), 'insiderFrameworkSystem');

        // Verificando as permissões do usuário atual X rota requisitada
        $routeData->validatePermissionsRoute();

        // Executando a rota
        $routeData->executeRoute();
    }

    /**
     * Função que requista uma rota de erro
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Request
     *
     * @param string $error Tipo do erro
     * @param array  $data  Dados adicionais do erro
     *
     * @return void
     */
    public static function requestErrorRoute(string $error, array $data): void
    {
        $defaultActions = \Modules\InsiderFramework\Core\KernelSpace::getVariable('defaultActions', 'RoutingSystem');
        $route = "";

        // Se existe um array de actions default
        if (is_array($defaultActions)) {
            // Se existe a action default deste erro
            if (isset($defaultActions[$error])) {
                $route = $defaultActions[$error];
            } else {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "The %" . $error . "% error is not set in the default error array",
                    "app/sys"
                );
            }
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("DefaultActions not defined on error " . $error);
        }

        $specificDomain = null;
        if (isset($data['specificDomain'])) {
            $specificDomain = $data['specificDomain'];
        }

        // Chamando rota de erro
        Request::requestRoute("/error/" . $route['method'], $specificDomain);
    }

    /**
     * Função que retorna o domínio de acesso atual
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Request
     *
     * @return string Domain of request
     */
    public static function getCurrentDomainOfRequest(): string
    {
        $specificDomain = null;
        $server = \Modules\InsiderFramework\Core\KernelSpace::getVariable('SERVER', 'insiderFrameworkSystem');

        // Se o domínio foi enviado via headers
        if (isset($server['HTTP_HOST'])) {
            $specificDomain = strtolower($server['REQUEST_SCHEME'] . "://" . $server['HTTP_HOST']);
        } else {
            // Se não existe variável do servidor, a rota é para todos os domínios
            $specificDomain = '*';
        }

        return $specificDomain;
    }

    /**
     * Função que busca a rota correspondente e devolve um objeto de rota
     * com todas as propriedades setadas
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Request
     *
     * @param string $routeNow             Rota que está sendo buscada
     * @param string $actionNow            Ação que está sendo buscada
     * @param string $originalUrlRequested URL original da requisição
     * @param string $specificDomain       Domínio de origem da requisição
     * @param bool   $inverted             Flag de inversão de lógica de busca
     *
     * @return RouteData Objeto da rota
     */
    public static function searchAndFillRouteData(
        string $routeNow,
        string $actionNow,
        string $originalUrlRequested,
        string $specificDomain = null,
        bool $inverted = false
    ): RouteData {
        // Buscando as actions padrão
        $defaultActions = \Modules\InsiderFramework\Core\KernelSpace::getVariable('defaultActions', 'RoutingSystem');

        // Rotas globais mapeadas
        $urlRoutes = \Modules\InsiderFramework\Core\KernelSpace::getVariable('urlRoutes', 'RoutingSystem');

        // Corrigindo o valor da rota atual
        if ($routeNow === "/") {
            $routeNow = "";
        }

        // Criando o objeto de rota
        $routeObj = new RouteData();
        $routeObj->setOriginalUrlRequested($originalUrlRequested);
        $routeObj->setRoute($routeNow);
        $routeObj->setActionNow($actionNow);

        // Setando o verbo da requisição
        $server = \Modules\InsiderFramework\Core\KernelSpace::getVariable('SERVER', 'insiderFrameworkSystem');
        if (isset($server['REQUEST_METHOD'])) {
            $routeObj->setHttpVerb($server['REQUEST_METHOD']);
        }

        // Se as rotas não foram definidas globalmente
        if ($urlRoutes === null) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister("No routes were recorded globally", "app/sys");
        }

        // Se o domínio completo não foi especificado
        if ($specificDomain == null) {
            $specificDomain = Request::getCurrentDomainOfRequest();
        }

        // Se o domínio está listado no array de rotas
        if (key_exists($specificDomain, $urlRoutes)) {
            $routeObj->setDomain($specificDomain);

            // Buscando a rota
            $routeObj->fillRouteData();
        }

        // Se encontrou uma rota
        if ($routeObj->getApp() !== null) {
            return $routeObj;
        } else {
            // Se o domínio não é o global e se existe o global
            if ($routeObj->getDomain() !== "*" && isset($urlRoutes["*"])) {
                // Busca novamente a rota no domínio global
                $routeObj->setDomain("*");
                $routeObj->fillRouteData();
            }
        }

        // Se encontrou uma rota
        if ($routeObj->getApp() !== null) {
            return $routeObj;
        } elseif ($inverted === false) {
            // Guardando o domínio original da request
            $domain = Request::getCurrentDomainOfRequest();

            // Buscando nas rotas globais
            // (se a rota é a home (vazia) e a action foi preenchida)
            if ($routeObj->getRoute() === "" && $routeObj->getActionNow() !== "") {
                // A rota será a action que está no objeto
                $route = $routeObj->getActionNow();

                // E a action será vazia
                $action = "";

                // Buscando tudo novamente...
                $reverted = true;
                $routeObj = Request::searchAndFillRouteData($route, $action, $originalUrlRequested, $domain, $reverted);

                // Se encontrou uma rota
                if ($routeObj->getApp() !== null) {
                    return $routeObj;
                } else {
                    http_response_code($defaultActions['NotFound']['responsecode']);
                    $routeObj = new RouteData();
                    $routeObj->setOriginalUrlRequested($originalUrlRequested);
                    $routeObj->setDomain("*");
                    $routeObj->setRoute($defaultActions['NotFound']['route']);
                    $routeObj->setActionNow($defaultActions['NotFound']['method']);
                    // Setando o verbo da requisição
                    if (isset($server['REQUEST_METHOD'])) {
                        $routeObj->setHttpVerb($server['REQUEST_METHOD']);
                    }
                    $routeObj->fillRouteData();
                }
            }
        }

        // Se não encontrou a rota, não tem app e é enfim, 404
        if ($routeObj->getApp() === null) {
            http_response_code($defaultActions['NotFound']['responsecode']);
            $routeObj = new RouteData();
            $routeObj->setOriginalUrlRequested($originalUrlRequested);
            $routeObj->setDomain("*");
            $routeObj->setRoute($defaultActions['NotFound']['route']);
            $routeObj->setActionNow($defaultActions['NotFound']['method']);
            // Setando o verbo da requisição
            if (isset($server['REQUEST_METHOD'])) {
                $routeObj->setHttpVerb($server['REQUEST_METHOD']);
            }
            $routeObj->fillRouteData();
        }

        return $routeObj;
    }
}
