<?php
namespace Controllers\start;
use Helpers\globalHelper;

/**
 * @Route (path="/test", defaultaction="gettest")
 * @Verbs (GET)
 */
class Api_Controller extends \KeyClass\Controller {
    
    /**
     *  @author Marcello Costa
     *
     *  Test method that returns a JSON
     *
     *  @Verbs (GET)
     *  @Route (path="gettest")
     *  @Permission(type="custom", rules="askToEnter") 
     *  @ResponseFormat(JSON)
     */
    public function gettest() {
        $message = array(
            'message' => 'API says hello!'
        );
        $this->responseAPI($message);
    }
    
    /**
     *  @author Marcello Costa
     *
     *  Test method that returns a JSON (POST)
     *
     *  @Verbs (POST)
     *  @Route (path="posttest")
     *  @Permission(type="custom", rules="askToEnter") 
     *  @ResponseFormat(JSON)
     */
    public function posttest() {
        $token = null;
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
          $matches = array();
          preg_match('/Bearer (.*)/', $headers['Authorization'], $matches);
          if (isset($matches[1])) {
            $token = $matches[1];
          }
        }

        $this->responseAPI($token);
    }
}
?>
