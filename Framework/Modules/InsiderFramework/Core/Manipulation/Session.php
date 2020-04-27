<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods for Session
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @link    https://www.insiderframework.com/documentation/keyclass
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\Manipulation\Session
 */
trait Session
{
    /**
     * Destroy the php session
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Session
     *
     * @return void
     */
    public static function destroySession(): void
    {
        $sessionParams = session_get_cookie_params();

        if (is_array($sessionParams)) {
            \Modules\InsiderFramework\Core\Manipulation\Cookie::setCookie(
                session_name(),
                '',
                $sessionParams['path'],
                time() - 36000,
                $sessionParams['domain'],
                $sessionParams['secure'],
                $sessionParams['httponly']
            );
        }

        session_destroy();
    }
}
