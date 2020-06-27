<?php

namespace Apps\Sys\Controllers;

/**
 * Class with global sys functions that can be called
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
     * Default action fake to satisfy routes file requirements
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
     * Function that records information about a cookie through a request via URL
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
        if ($overwrite == true) {
            \Modules\InsiderFramework\Core\Manipulation\Cookie::setCookie(
                $cookiename,
                $value,
                null,
                null,
                null,
                0,
                false
            );
        } else {
            // Checando o cookie
            $cookie_exist = \Modules\InsiderFramework\Core\Manipulation\Cookie::getCookie($cookiename);

            // Se o cookie não existir
            if ($cookie_exist === null) {
                \Modules\InsiderFramework\Core\Manipulation\Cookie::setCookie(
                    $cookiename,
                    $value,
                    null,
                    null,
                    null,
                    0,
                    false
                );
            }
        }
    }

    /**
     * Function that retrieves information from a cookie through a request via URL
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
