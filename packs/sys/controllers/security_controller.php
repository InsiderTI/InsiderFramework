<?php
/**
  Controller de segurança do pack sys
*/

// Namespace relativo ao pack do controller
namespace Controllers\sys;

/**
  Classe responsável pela segunda camada de segurança
 
  @author Marcello Costa
  
  @package Controllers\sys\Security_Controller
  
  @Route (path="/security", defaultaction="getNativeAccessLevel")
 */
class Security_Controller extends \KeyClass\Controller{
    /**
        Função para renovar o login do usuário
     
        @author Marcello Costa
  
        @package Controllers\sys\Security_Controller
     
        @return  bool   Resultado da operação
    */
    private function renewAccess():bool {
        // Exemplo de código de renovação da sessão/cookie do usuário
        // Se o cookie existe
        $cookieName = 'sec_cookie';
        if (\KeyClass\Security::checkCookie($cookieName)) {
          // Renovando o cookie
          $cookieValue=\KeyClass\Security::getCookie($cookieName);
          \KeyClass\Security::setCookie($cookieName, $cookieValue);

            return true;
        }
        else {
            return false;
        }
    }

    /**
        Retorna as permissões atuais de forma customizada
        pelo desenvolvedor.
     
        @author Marcello Costa
  
        @package Controllers\sys\Security_Controller
      
        @return  mixed  Qualquer retorno que o desenvolver desejar
    */
    public function getCustomAccessLevel() {
        return true;
    }

    /**
        Função que verifica permissões da rota e toda uma ação com base
        no que foi configurado. Aqui o desenvolvedor pode tomar uma ação
        específica e até mesmo impedir o curso natural de processamento
        do framework se setar a variável $access como null
     
        @author Marcello Costa
  
        @package Controllers\sys\Security_Controller
     
        @param  Modules\insiderRoutingSystem\routeData  $routeObj    Objeto da rota
        @param  mixed  $permissionNow    Permissões atuais do usuário
        @param  bool  $access           Variável de controle de acesso
      
        @return  mixed  Qualquer retorno que o desenvolver desejar
    */
    public function validateCustomACLPermission($routeObj, $permissionNow, &$access) {
        // Aqui está implementado um código de exemplo de validação customizada.
        // Como dito na descrição do método, se a variável $access for setada
        // como null ($access = null;), o framework não fará nenhuma ação após
        // o término do processamento deste método, ficando a cargo do desenvolver
        // criar uma lógica de rotamento adicional personalizada
        $access = $permissionNow;
        
        // Habilite esta linha para ativar a renovação automática da
        // sessão/cookie do usuário em cada requisição
        // renewAccess();
    }
}
?>