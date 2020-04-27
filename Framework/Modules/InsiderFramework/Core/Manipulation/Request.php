<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods responsible for handle requests
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Manipulation\Request
 */
trait Request
{
    /**
    * Clear and restart the php buffer
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Manipulation\Request
    *
    * @return void
    */
    public static function clearAndRestartBuffer(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
    }

    /**
     * Sets the post info inside the global variable
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Request
     *
     * @param array $post      Post content
     * @param bool  $overwrite Overwrite flag
     *
     * @return void
     */
    public static function setPost(array $post, bool $overwrite = true): void
    {
        if ($overwrite) {
            \Modules\InsiderFramework\Core\Manipulation\Cryptography::clearPost();

            $_REQUEST['POST'] = $post;
        } else {
            if (is_array($post)) {
                $_REQUEST['POST'] = array_merge(\Modules\InsiderFramework\Core\Request::getPost(), $post);
            } else {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Error inserting information in post: variable %" . $post . "% not a valid array!",
                    "app/sys"
                );
            }
        }
    }

    /**
     * Erase all the information insde the post global variable
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Request
     *
     * @return void
     */
    public static function clearPost(): void
    {
        $_REQUEST['POST'] = null;
    }

    /**
     * Function that takes post information from the global request array
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Request
     *
     * @return array|null Post information
     */
    public static function getPost(): ?array
    {
        return (\Modules\InsiderFramework\Core\Request::getRequest('post'));
    }

    /**
     * Function that takes get information from global array and request
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Request
     *
     * @return array|null Get information
     */
    public static function getGet(): ?array
    {
        return (\Modules\InsiderFramework\Core\Request::getRequest('get'));
    }

    /**
     * Gets filtered value of request variables
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Request
     *
     * @param string $type What will be returned from the request array
     *
     * @return array|null Data returned from request
     */
    public static function getRequest(string $type): ?array
    {
        switch (strtolower($type)) {
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
                } else {
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

    /**
     * Convert an string received in the request
     *
     * @author Marcello Costa <marcello88costa@yahoo.com.br>
     *
     * @package Modules\InsiderFramework\Core\Controller
     *
     * @param string $data        Data received in request
     * @param bool   $origjson    If the data received are JSON
     * @param bool   $returnarray If the return must be an array
     *
     * @return mixed Unknown type of return
     */
    protected function convertDataOfPost(
        string $data,
        bool $origjson = true,
        bool $returnarray = true
    ) {
        // If it's an JSON
        if ($origjson) {
            $newdata = str_replace("\/", "/", $data);

            // If the data converted are an valid JSON
            if (Json::isJSON($newdata)) {
                // If the return it's an array
                if ($returnarray === true) {
                    return (json_decode($newdata, true));
                } else {
                    return (json_decode($newdata));
                }
            } else {
                return false;
            }
        } else {
            $newdata = str_replace("\/", "/", $data);
            return $newdata;
        }
    }
}
