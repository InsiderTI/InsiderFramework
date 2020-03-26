<?php

/**
  KeyClass\Route
*/

namespace KeyClass;

require_once("frame_src" . DIRECTORY_SEPARATOR . "keyclasses" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "security.php");
require_once("frame_src" . DIRECTORY_SEPARATOR . "keyclasses" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "request.php");

/**
  KeyClass for handling routes

  @package KeyClass\Route

  @author Marcello Costa
 */
class Route {
    /** @var \Modules\insiderRoutingSystem\routeData Dados da rota */
    private $routeData;
    
    /**
      Object build function

      @author Marcello Costa

      @package KeyClass\Route

      @return void
     */
    public function __construct() {
        $this->routeData = new \Modules\insiderRoutingSystem\routeData();
    }
}
