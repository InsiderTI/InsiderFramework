<?php
/**
  Main controller
*/
namespace Controllers\start;

/**
  Main Controller
  
  @author Marcello Costa
  @package Controllers\start\Main_Controller
  
  @Route(path="/", defaultaction="home")
  @Verbs(POST,GET)
 */
class Main_Controller extends \KeyClass\Controller {
    /**
       Home

       @author Marcello Costa
       @package Controllers\start\Main_Controller

       @Route(path="home")
       @Permission(type="custom", rules="homeRule")
       @Cache(none)
    */
    public function home() {
        // Renderview
        $this->renderView('start::home.sgv');
    }
}
?>
