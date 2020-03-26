<?php
/**
  KeyClass\Security
*/

namespace KeyClass;

use KeyClass\Model;
use ioncube\phpOpensslCryptor\Cryptor;

/**
   KeyClass containing security and session functions

   @package KeyClass\Security

   @author Marcello Costa
*/
class Security{
    /**
        
        Function that returns user access level for a route
      
        @author Marcello Costa

        @package KeyClass\Security
      
        @param  routeData  $routeObj    Object of the current route

        @return  mixed  Returns the access level
    */
    public static function getUserAccessLevel(\Modules\insiderRoutingSystem\routeData $routeObj) {
        global $kernelspace;
        $ajaxrequest = $kernelspace->getVariable('ajaxrequest', 'insiderRoutingSystem');
        $permissions = $routeObj->getPermissions();
        
        if (file_exists(INSTALL_DIR.DIRECTORY_SEPARATOR.'packs'.DIRECTORY_SEPARATOR.'sys'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'security_controller.php')) {
            switch (strtolower($permissions['type'])){
                case 'native':
                    return \Modules\insiderRoutingSystem\Permission::getNativeAccessLevel();
                break;
                case 'custom':
                    \KeyClass\FileTree::requireOnceFile(INSTALL_DIR.DIRECTORY_SEPARATOR.'packs'.DIRECTORY_SEPARATOR.'sys'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'security_controller.php');
                    $SecurityController = new \Controllers\sys\Security_Controller('sys', null, $ajaxrequest);

                    return $SecurityController->getCustomAccessLevel();
                break;
                default:
                    primaryError('ACL_METHOD not recognized');
                break;
            }
        }
        else {
            \KeyClass\Error::i10nErrorRegister("Security controller not found for user privilege verification", 'pack/sys');
        }
    }

    /**
        Destroy the php session
     
        @author Marcello Costa

        @package KeyClass\Security

        @return void Without return
    */
    public static function destroySession() : void {
        $sessionParams = session_get_cookie_params();
        
        if (is_array($sessionParams)) {
            \KeyClass\Security::setCookie(
                session_name(), 
                '', 
                $sessionParams['path'], 
                time()-36000, 
                $sessionParams['domain'], 
                $sessionParams['secure'], 
                $sessionParams['httponly']
            );
        }
        
        session_destroy();
    }

    /**
        Checks if a cookie exist
     
        @author Marcello Costa

        @package KeyClass\Security
     
        @param  string           $cookieName  Name of the cookie
        @param  string|int|bool  $value       Expected value of the cookie
     
        @return  bool  Validation return
    */
    public static function checkCookie(string $cookieName, $value=NULL) : bool {
        if (isset($_COOKIE[$cookieName])) {
            if ($value !== NULL) {
                if (\KeyClass\Security::getRequest('cookie')[$cookieName] !== $value) {
                    return false;
                }
                else {
                    return true;
                }
            }
            
            return true;
        }

        return false;
    }

    /**
        Create a cookie
     
        @author Marcello Costa

        @package KeyClass\Security
     
        @param  string  $name           Cookie name
        @param  string  $cookievalue    Cookie value
        @param  string  $path           Path of cookie. If it's null, the cookie
                                        can be used by the entire site.
        @param  string  $expiretime     Expire time
        @param  string  $domain         Domain (url) of cookie
        @param  bool    $https          Indicates that cookie can only be transmitted throw an safe connection (HTTPS).
        @param  bool    $htmlOnly       When this is true the cookie will be only valid for http protocol. It's means
                                        that the cookie cannot be accessed by script languages (like JavaScript).
                                        Default value: false;
     
        @return void Without return
    */
    public static function setCookie(string $name, string $cookievalue=null, string $path=null, string $expiretime=null, string $domain=null, bool $https=false, bool $htmlOnly=false) : void {
        if ($cookievalue == null) {
            $cookievalue=time().uniqid();

            $ipuser=getenv("REMOTE_ADDR");

            $cookievalue = \KeyClass\Security::encryptString($cookievalue.$ipuser);
        }

        if ($path == null) {
            $path="/";
        }

        if ($expiretime == null) {
            $expiretime=time()+3600*24;
        }

        if ($domain == null) {
            $domain=str_replace("http://","",REQUESTED_URL);
            $domain=str_replace("https://","",$domain);
        }

        setcookie($name, $cookievalue, $expiretime, $path, $domain, $https, $htmlOnly);
    }

    /**
        Alias function for unset cookies

        @author Marcello Costa

        @package KeyClass\Security
     
        @param  string  $name    Cookie name
     
        @return void Without return
    */
    public static function RemoveCookie(string $name) : void {
        if (isset(\KeyClass\Security::getRequest('cookie')[$name])) {
            unset($_COOKIE[$name]);
        }
    }

    /**
        Function that captures and treats the value of a cookie with the 
        addslashes function
     
        @author Marcello Costa

        @package KeyClass\Security
     
        @param  string  $name    Name of the cookie
     
        @return  string  Value of the cookie
    */
    public static function getCookie(string $name) : ?string {
        if (\KeyClass\Security::checkCookie($name)) {
            $cookieValue = \KeyClass\Security::getRequest('cookie')[$name];
            return (htmlspecialchars(addslashes($cookieValue)));
        }

        else {
            return null;
        }
    }

    /**
        Encrypt a string

        @author Marcello Costa

        @package KeyClass\Security
     
        @param  string  $string    String to be encripted
        @param  string  $key       Encription key
        @param  bool    $md5       If this is true, return a MD5 string
     
        @return  string  Encripted string
    */
    public static function encryptString(string $string, string $key = null, bool $md5=false) : string {
        if ($string !== NULL) {
            if ($key === null) {
                $key = ENCRYPT_KEY;
            }
            $encrypted = Cryptor::Encrypt($string, $key);

            if ($md5 === false) {
                return $encrypted;
            }
            else {
                return md5($encrypted);
            }
        }
        else {
            primaryError('String for encryption has not been specified');
        }
    }

    /**
        Decrypt a string

        @author Marcello Costa

        @package KeyClass\Security

        @param  string  $string    String to be decripted
        @param  string  $key       Decription key
     
        @return  string  Decrypted string
    */
    public static function decryptString(string $string, string $key = null) : string {
        if ($string !== NULL) {
            if ($key === null) {
                $key = ENCRYPT_KEY;
            }
            $decrypted = Cryptor::Decrypt($string, $key);

            return $decrypted;
        }
        
        throw new \Exception('String for decryption has not been specified');
    }

    /**
        Sets the post info inside the global variable
      
        @author Marcello Costa
     
        @package KeyClass\Security
     
        @param  array  $post       Post content
        @param  bool   $overwrite  Overwrite flag
     
        @return void Without return
    */
    public static function setPost(array $post, bool $overwrite=true) : void {
        if ($overwrite) {
            \KeyClass\Security::clearPost();

            $_REQUEST['POST']=$post;
        }

        else {
            if (is_array($post)) {
                $_REQUEST['POST'] = array_merge(\KeyClass\Security::getPost(), $post);
            }
            else {
                \KeyClass\Error::i10nErrorRegister("Error inserting information in post: variable %".$post."% not a valid array!", 'pack/sys');
            }
        }
    }

    /**
        Erase all the information insde the post global variable
     
        @author Marcello Costa
     
        @package KeyClass\Security
     
        @return void Without return
    */
    public static function clearPost() : void {
        $_REQUEST['POST'] = null;
    }

    /**
        Function that takes post information from the global request array
     
        @author Marcello Costa
     
        @package KeyClass\Security
     
        @return array Post information
    */
    public static function getPost() : ?array {
        return (\KeyClass\Security::getRequest('post'));
    }

    /**
        Function that takes get information from global array and request
     
        @author Marcello Costa
     
        @package KeyClass\Security
     
        @return array Get information
    */
    public static function getGet() : ?array {
        return (\KeyClass\Security::getRequest('get'));
    }

    /**
        Gets filtered value of request variables
     
        @author Marcello Costa
     
        @package KeyClass\Security
     
        @param  string  $type    What will be returned from the request array
     
        @return  array  Data returned from request
    */
    public static function getRequest(string $type) : ?array {
        switch(strtolower($type)) {
            case "get":
                return filter_input_array(INPUT_GET);
            break;

            case "post":
                return filter_input_array(INPUT_POST);
            break;

            case "cookie":
                return filter_input_array(INPUT_COOKIE);
            break;

            case "server":
                return filter_input_array(INPUT_SERVER);
            break;

            case "env":
                return filter_input_array(INPUT_ENV);
            break;

            case "session":
                // @todo Must be implemented
                if (isset($_SESSION)) {
                    return $_SESSION;
                }
                else {
                    return [];
                }
            break;

            case "request":
                // @todo Must be implemented 
                return $_REQUEST;
            break;

            default:
                return false;
            break;
        }
    }
}
