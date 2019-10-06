<?php

// Namespace insiderRoutingSystem
namespace Modules\insiderRoutingSystem;

/**
  Classe de requisição de rota do módulo de roteamento

  @package insiderRoutingSystem\Request

  @author Marcello Costa
 */
class Request {
    /**
      Função que requista uma URL

      @author Marcello Costa

      @package insiderRoutingSystem\Route

      @param  string  $url    URL requisitada
      @param  string  $specificDomain    Domínio específico da requisição

      @return void
     */
    public static function requestRoute(string $url, string $specificDomain = null) : void {
        if ($url === null){
            primaryError("Url not specified");
        }
      
        // Verifica a validade uma rota
        $routeAndAction = \Modules\insiderRoutingSystem\Validation::getRouteAndActionDataFromUrl($url);

        // Setando a configuração de ajaxRequest
        global $kernelspace;
        $kernelspace->setVariable(array('ajaxrequest' => $routeAndAction['ajaxRequest']), 'insiderRoutingSystem');
        
        // Busca a rota correspondente
        $routeData = \Modules\insiderRoutingSystem\Request::SearchAndFillRouteData(
            $routeAndAction['routeNow'], 
            $routeAndAction['actionNow'], 
            $routeAndAction['originalUrlRequested'], 
            $specificDomain
        );
        
        // Setando a configuração de responseFormat
        $kernelspace->setVariable(array('responseFormat' => $routeData->getResponseFormat()), 'insiderFrameworkSystem');
        
        // Verificando as permissões do usuário atual X rota requisitada
        $routeData->ValidatePermissionsRoute();
        
        // Executando a rota
        $routeData->executeRoute();
    }
    
    /**
      Função que requista uma rota de erro

      @author Marcello Costa

      @package insiderRoutingSystem\Route

      @param  string  $error    Tipo do erro
      @param  array   $data     Dados adicionais do erro

      @return void Without return
     */
    public static function requestErrorRoute(string $error, array $data) : void {
        global $kernelspace;
        $defaultActions = $kernelspace->getVariable('defaultActions', 'insiderRoutingSystem');
        $route = "";
        
        // Se existe um array de actions default
        if (is_array($defaultActions)) {
            // Se existe a action default deste erro
            if (isset($defaultActions[$error])) {
                $route = $defaultActions[$error];
            } else {
                \KeyClass\Error::i10nErrorRegister(
                    "The %" . $error . "% error is not set in the default error array", 'pack/sys'
                );
            }
        }
        else{
            primaryError("DefaultActions not defined on error ".$error);
        }
        
        $specificDomain = null;
        if (isset($data['specificDomain'])){
            $specificDomain = $data['specificDomain'];
        }
        
        // Chamando rota de erro
        Request::requestRoute("/error/".$route['method'], $specificDomain);
    }
    
    /**
      Função que retorna o domínio de acesso atual

      @author Marcello Costa

      @package insiderRoutingSystem\Request

      @return string Domínio
     */
    public static function getDomainNow() : string {
        global $kernelspace;

        $specificDomain = null;
        $server = $kernelspace->getVariable('SERVER', 'insiderFrameworkSystem');
        
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
      Função que busca a rota correspondente e devolve um objeto de rota
      com todas as propriedades setadas

      @author Marcello Costa

      @package insiderRoutingSystem\Request
     
     @param  string  $routeNow               Rota que está sendo buscada
     @param  string  $actionNow              Ação que está sendo buscada
     @param  string  $originalUrlRequested   URL original da requisição
     @param  string  $specificDomain         Domínio de origem da requisição
     @param  bool    $inverted               Flag de inversão de lógica de busca


      @return RouteData Objeto da rota
     */
    public static function SearchAndFillRouteData(string $routeNow, 
              string $actionNow, string $originalUrlRequested, 
              string $specificDomain = null, 
              bool $inverted = false) : \Modules\insiderRoutingSystem\RouteData {

        global $kernelspace;
        
        // Buscando as actions padrão
        $defaultActions = $kernelspace->getVariable('defaultActions', 'insiderRoutingSystem');
        
        // Rotas globais mapeadas
        $urlRoutes = $kernelspace->getVariable('urlRoutes', 'insiderRoutingSystem');
        
        // Corrigindo o valor da rota atual
        if ($routeNow === "/") {
            $routeNow="";
        }
        
        // Criando o objeto de rota
        $routeObj = new routeData();
        $routeObj->setOriginalUrlRequested($originalUrlRequested);
        $routeObj->setRoute($routeNow);
        $routeObj->setActionNow($actionNow);
        
        // Setando o verbo da requisição
        $server = $kernelspace->getVariable('SERVER', 'insiderFrameworkSystem');
        if (isset($server['REQUEST_METHOD'])){
            $routeObj->setVerb($server['REQUEST_METHOD']);
        }
        
        // Se as rotas não foram definidas globalmente
        if ($urlRoutes === null) {
            \KeyClass\Error::i10nErrorRegister("No routes were recorded globally", 'pack/sys');
        }

        // Se o domínio completo não foi especificado
        if ($specificDomain == null) {
            $specificDomain = Request::getDomainNow();
        }

        // Se o domínio está listado no array de rotas
        if (key_exists($specificDomain, $urlRoutes)) {
            $routeObj->setDomain($specificDomain);
            
            // Buscando a rota
            $routeObj->fillRouteData();
        }
        
        // Se encontrou uma rota
        if ($routeObj->getPack() !== null){
            return $routeObj;
        }
        
        // Se não tem pack, a rota não foi encontrada (ou nada foi procurado)
        else{
            // Se o domínio não é o global e se existe o global
            if ($routeObj->getDomain() !== "*" && isset($urlRoutes["*"])){
                // Busca novamente a rota no domínio global
                $routeObj->setDomain("*");
                $routeObj->fillRouteData();
            }
        }
        
        // Se encontrou uma rota
        if ($routeObj->getPack() !== null){
            return $routeObj;
        }
        
        // Se chegou até aqui sem pack, hora de tentar inverter a lógica
        else if ($inverted === false){
            // Guardando o domínio original da request
            $domain = Request::getDomainNow();
            
            // Buscando nas rotas globais
            // (se a rota é a home (vazia) e a action foi preenchida)
            if ($routeObj->getRoute() === "" && $routeObj->getActionNow() !== "") {
                // A rota será a action que está no objeto
                $route=$routeObj->getActionNow();

                // E a action será vazia
                $action="";
                
                // Buscando tudo novamente...
                $reverted = true;
                $routeObj = Request::SearchAndFillRouteData($route, $action, $originalUrlRequested, $domain, $reverted);
                
                // Se encontrou uma rota
                if ($routeObj->getPack() !== null){
                    return $routeObj;
                }
                // Se chegou até aqui e não encontrou uma rota
                else{
                    http_response_code($defaultActions['NotFound']['responsecode']);
                    $routeObj = new routeData();
                    $routeObj->setOriginalUrlRequested($originalUrlRequested);
                    $routeObj->setDomain("*");
                    $routeObj->setRoute($defaultActions['NotFound']['route']);
                    $routeObj->setActionNow($defaultActions['NotFound']['method']);
                    // Setando o verbo da requisição
                    if (isset($server['REQUEST_METHOD'])){
                        $routeObj->setVerb($server['REQUEST_METHOD']);
                    }
                    $routeObj->fillRouteData();
                }
            }
        }

        // Se não encontrou a rota, não tem pack e é enfim, 404
        if ($routeObj->getPack() === null) {
            http_response_code($defaultActions['NotFound']['responsecode']);
            $routeObj = new routeData();
            $routeObj->setOriginalUrlRequested($originalUrlRequested);
            $routeObj->setDomain("*");
            $routeObj->setRoute($defaultActions['NotFound']['route']);
            $routeObj->setActionNow($defaultActions['NotFound']['method']);
            // Setando o verbo da requisição
            if (isset($server['REQUEST_METHOD'])){
                $routeObj->setVerb($server['REQUEST_METHOD']);
            }
            $routeObj->fillRouteData();
        }

        return $routeObj;
    }
}
