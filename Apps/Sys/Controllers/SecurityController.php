<?php

namespace Apps\Sys\Controllers;

/**
 * Class responsible for the second layer of security
 *
 * @author Marcello Costa
 *
 * @package Apps\Sys\Controllers\SecurityController
 *
 * @Route (path="/security", defaultaction="getCustomAccessLevel")
 */
class SecurityController extends \Modules\InsiderFramework\Core\Controller
{
    /**
     * Function to renew user login
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\SecurityController
     *
     * @return bool Processing result
    */
    protected function renewAccess(string $cookieName = 'sec_cookie'): bool
    {
        // User session / cookie renewal code example
        if (\Modules\InsiderFramework\Core\Validation\Cookie::checkCookie($cookieName)) {
            $cookieValue = \Modules\InsiderFramework\Core\Manipulation\Cookie::getCookie($cookieName);
            \Modules\InsiderFramework\Core\Manipulation\Cookie::setCookie($cookieName, $cookieValue);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the current permissions in a customized way
     * by the developer.
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\SecurityController
     *
     * @return mixed Any feedback that you develop
    */
    public function getCustomAccessLevel()
    {
        return true;
    }

    /**
     * Function that checks route permissions and an entire action based
     * what has been configured. Here the developer can take action
     * specific and even prevent the natural course of processing
     * of the framework if you set the $ access variable to null
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\SecurityController
     *
     * @param RouteData  $routeObj      Route object
     * @param mixed      $permissionNow Current user permissions
     * @param bool       $access        Access control variable
     *
     * @return mixed Any feedback that you develop
    */
    public function validateCustomAclPermission($routeObj, $permissionNow, &$access)
    {
        // Here is a sample code for custom validation.
        // As stated in the method description, if the $access variable is set
        // as null ($access = null;), the framework will take no action after
        // the end of processing this method, being in charge of developing
        // create additional custom routing logic
        $access = $permissionNow;
        
        // Enable this line to enable automatic renewal of the
        // user session / cookie on each request
        // renewAccess();
    }
}
