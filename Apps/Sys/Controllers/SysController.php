<?php

namespace Apps\Sys\Controllers;

/**
 * Classe com as funções globais do app sys que podem ser chamadas
 * via request
 *
 * @author Marcello Costa
 *
 * @package Apps\Sys\Controllers\SysController
 *
 * @Route(path="/sys", defaultaction="defaultaction")
 */
class SysController extends \Modules\InsiderFramework\Core\Controller
{
    /**
     * Default action fake para satisfazer as exigências do arquivo routes
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\SysController
     *
     * @Route (path="defaultaction")
     *
     * @return void
    */
    public function defaultAction(): void
    {
    }

    /**
     * Função que grava informações de um cookie atráves de um requisiçao via URL
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\SysController
     *
     * @Route (path="setdatacookie/{cookiename}/{value}/{overwrite}")
     * @Param (cookiename='(.*)')
     * @Param (value='(.*)')
     * @Param (overwrite='true|false')
     *
     * @param string $cookiename Cookie name to be set
     * @param string $value      Cookie value
     * @param bool   $overwrite  Overwrite cookie data
     *
     * @return void
    */
    public function setDataCookie(string $cookiename, string $value, bool $overwrite): void
    {
        // Se for para sobreescrever
        if ($overwrite == true) {
            \Modules\InsiderFramework\Core\Manipulation\Cookie::setCookie($cookiename, $value, null, null, null, 0, false);
        }

        // Se não for para sobreescrever
        else {
            // Checando o cookie
            $cookie_exist = \Modules\InsiderFramework\Core\Manipulation\Cookie::getCookie($cookiename);

            // Se o cookie não existir
            if ($cookie_exist === null) {
                \Modules\InsiderFramework\Core\Manipulation\Cookie::setCookie($cookiename, $value, null, null, null, 0, false);
            }
        }
    }

    /**
     * Função que recupera informações de um cookie atráves de um requisiçao via URL
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\SysController
     *
     * @Route (path="getdatacookie/{cookiename}/{returnjson}")
     * @Param (cookiename='(.*)')
     * @Param (returnjson='true|false')
     *
     * @param  string $cookiename Cookie name to be getted
     * @param  bool   $returnjson If return is a json or not
     * @return void
    */
    public function getDataCookie(string $cookiename, bool $returnjson = null): void
    {
        $datacookie = \Modules\InsiderFramework\Core\Manipulation\Cookie::getCookie($cookiename);

        // Se for um dado no formato json, tratar a string
        if ($returnjson) {
            $datacookie = str_replace("\\", "", $datacookie);
        }
        
        if ($returnjson) {
            $this->responseJson($datacookie);
        } else {
            echo $datacookie;
        }
    }
}
