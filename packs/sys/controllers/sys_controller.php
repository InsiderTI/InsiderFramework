<?php
/**
  Controller com funções globais do pack sys
*/

// Namespace relativo ao pack do controller
namespace Controllers\sys;

/**
  Classe com as funções globais do pack sys que podem ser chamadas
  via request
  
  @author Marcello Costa
  
  @package Controllers\sys\Sys_Controller
  
  @Route(path="/sys", defaultaction="defaultaction")
 */
class Sys_Controller extends \KeyClass\Controller{
    /**
        Default action fake para satisfazer as exigências do arquivo routes
     
        @author Marcello Costa

        @package Controllers\sys\Sys_Controller
     
        @Route (path="defaultaction")
     
        @return Void
    */
    public function defaultaction() {
    }

    /**
        Função que grava informações de um cookie atráves de um requisiçao via URL
     
        @author Marcello Costa
      
        @package Controllers\sys\Sys_Controller
     
        @Route (path="setdatacookie/{namecookie}/{value}/{overwrite}")
        @Param (namecookie='(.*)')
        @Param (value='(.*)')
        @Param (overwrite='true|false')
     
        @return void Without return
    */
    public function setdatacookie($namecookie, $value, $overwrite) {
        // Se for para sobreescrever
        if ($overwrite == true) {
            \KeyClass\Security::setCookie($namecookie, $value, null, null, null, 0, false);
        }

        // Se não for para sobreescrever
        else {
            // Checando o cookie
            $cookie_exist=\KeyClass\Security::getCookie($namecookie);

            // Se o cookie não existir
            if ($cookie_exist === null) {
                \KeyClass\Security::setCookie($namecookie, $value, null, null, null, 0, false);
            }
        }
    }

    /**
        Função que recupera informações de um cookie atráves de um requisiçao via URL
     
        @author Marcello Costa

        @package Controllers\sys\Sys_Controller
     
        @Route (path="getdatacookie/{cookiename}/{returnjson}")
        @Param (cookiename='(.*)')
        @Param (returnjson='true|false')

        @return  array   Dados do cookie
    */
    public function getdatacookie($cookiename, $returnjson = null) {
        $datacookie = \KeyClass\Security::getCookie($cookiename);

        // Se for um dado no formato json, tratar a string
        if ($returnjson == "true") {
            $datacookie=str_replace("\\", "", $datacookie);
        }
        
        if ($returnjson == "true"){
            $this->responseJSON($datacookie);
        }
        else{
            echo $datacookie;
        }
    }
}
?>
