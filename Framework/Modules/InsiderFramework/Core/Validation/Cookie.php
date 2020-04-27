<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Validation methods for cookies
 *
 * @package Modules\InsiderFramework\Core\Validation\Cookie
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link    https://www.insiderframework.com/documentation/keyclass
 */
trait Cookie
{
    /**
     * Checks if a cookie exist
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Cookie
     *
     * @param string          $cookieName Name of the cookie
     * @param string|int|bool $value      Expected value of the cookie
     *
     * @return bool Validation return
     */
    public static function checkCookie(string $cookieName, $value = null): bool
    {
        if (isset($_COOKIE[$cookieName])) {
            if ($value !== null) {
                if (\Modules\InsiderFramework\Core\Request::getRequest('cookie')[$cookieName] !== $value) {
                    return false;
                } else {
                    return true;
                }
            }

            return true;
        }

        return false;
    }
}
