<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods responsible for handle cookies
 *
 * @author   Marcello Costa <marcello88costa@yahoo.com.br>
 * @link     https://www.insiderframework.com/documentation/keyclass
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package  Modules\InsiderFramework\Core\Manipulation\Cookie
 */
trait Cookie
{
    /**
     * Create a cookie
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Cookie
     *
     * @param string $name        Cookie name
     * @param string $cookievalue Cookie value
     * @param string $path        Path of cookie. If it's null, the cookie
     *                            can be used by the entire site.
     * @param string $expiretime  Expire time
     * @param string $domain      Domain (url) of cookie
     * @param bool   $https       Indicates that cookie can only be transmitted throw an safe connection (HTTPS).
     * @param bool   $htmlOnly    When this is true the cookie will be only valid for http protocol. It's means
     *                            that the cookie cannot be accessed by script languages (like JavaScript).
     *                            Default value: false;
     *
     * @return void
     */
    public static function setCookie(
        string $name,
        string $cookievalue = null,
        string $path = null,
        string $expiretime = null,
        string $domain = null,
        bool $https = false,
        bool $htmlOnly = false
    ): void {
        if ($cookievalue == null) {
            $cookievalue = time() . uniqid();

            $ipuser = getenv("REMOTE_ADDR");

            $cookievalue = \Modules\InsiderFramework\Core\Manipulation\Cryptography::encryptString(
                $cookievalue . $ipuser
            );
        }

        if ($path == null) {
            $path = "/";
        }

        if ($expiretime == null) {
            $expiretime = time() + 3600 * 24;
        }

        if ($domain == null) {
            $domain = str_replace("http://", "", REQUESTED_URL);
            $domain = str_replace("https://", "", $domain);
        }

        setcookie($name, $cookievalue, $expiretime, $path, $domain, $https, $htmlOnly);
    }

    /**
     * Alias function for unset cookies
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Cookie
     *
     * @param string $name Cookie name
     *
     * @return void
     */
    public static function removeCookie(string $name): void
    {
        if (isset(\Modules\InsiderFramework\Core\Request::getRequest('cookie')[$name])) {
            unset($_COOKIE[$name]);
        }
    }

    /**
     * Function that captures and treats the value of a cookie with the
     * addslashes function
     *
     * @author Marcello Costa <marcello88costa@yahoo.com.br>
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Cookie
     *
     * @param string $name Name of the cookie
     *
     * @return string|null Value of the cookie
     */
    public static function getCookie(string $name): ?string
    {
        if (\Modules\InsiderFramework\Core\Validation\Cookie::checkCookie($name)) {
            $cookieValue = \Modules\InsiderFramework\Core\Request::getRequest('cookie')[$name];
            return (htmlspecialchars(addslashes($cookieValue)));
        } else {
            return null;
        }
    }
}
