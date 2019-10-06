<?php

/**
  Arquivo KeyClass\Route
 */
// Namespace das KeyClass

namespace KeyClass;

// Adicionando a classe Security
require_once("frame_src" . DIRECTORY_SEPARATOR . "keyclasses" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "security.php");

// Adicionando a classe Request
require_once("frame_src" . DIRECTORY_SEPARATOR . "keyclasses" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "request.php");

/**
  KeyClass responsável por tratar rotas

  @package KeyClass\Route

  @author Marcello Costa
 */
class Route {
    /** @var \Modules\insiderRoutingSystem\routeData Dados da rota */
    private $routeData;
    
    /**
      Função de construção do objeto

      @author Marcello Costa

      @package KeyClass\Route

      @return void
     */
    public function __construct() {
        $this->routeData = new \Modules\insiderRoutingSystem\routeData();
    }
}
