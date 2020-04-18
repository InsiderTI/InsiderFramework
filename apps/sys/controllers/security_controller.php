<?php

namespace Controllers\sys;

/**
 * Classe responsável pela segunda camada de segurança
 *
 * @author Marcello Costa
 *
 * @package Controllers\sys\SecurityController
 *
 * @Route (path="/security", defaultaction="getNativeAccessLevel")
 */
class SecurityController extends \Modules\InsiderFramework\Core\Controller
{
    /**
     * Função para renovar o login do usuário
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\SecurityController
     *
     * @return bool Processing result
    */
    protected function renewAccess(): bool
    {
        // Exemplo de código de renovação da sessão/cookie do usuário
        // Se o cookie existe
        $cookieName = 'sec_cookie';
        if (\Modules\InsiderFramework\Core\Validation\Cookie::checkCookie($cookieName)) {
          // Renovando o cookie
            $cookieValue = \Modules\InsiderFramework\Core\Manipulation\Cookie::getCookie($cookieName);
            \Modules\InsiderFramework\Core\Manipulation\Cookie::setCookie($cookieName, $cookieValue);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Retorna as permissões atuais de forma customizada
     * pelo desenvolvedor.
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\SecurityController
     *
     * @return mixed Qualquer retorno que o desenvolver desejar
    */
    public function getCustomAccessLevel()
    {
        return true;
    }

    /**
     * Função que verifica permissões da rota e toda uma ação com base
     * no que foi configurado. Aqui o desenvolvedor pode tomar uma ação
     * específica e até mesmo impedir o curso natural de processamento
     * do framework se setar a variável $access como null
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\SecurityController
     *
     * @param RouteData  $routeObj      Objeto da rota
     * @param mixed      $permissionNow Permissões atuais do usuário
     * @param bool       $access        Variável de controle de acesso
     *
     * @return mixed Qualquer retorno que o desenvolver desejar
    */
    public function validateCustomAclPermission($routeObj, $permissionNow, &$access)
    {
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
