<?php
/**
  Arquivo KeyClass\Codeinjected
*/
namespace Sagacious;

/**
  Classe principal do componente Codeinjected (SgsComponent)
  
  @author Marcello Costa

  @package Sagacious\SgsComponent\Codeinjected
*/
class Codeinjected extends \Sagacious\SgsComponent{
    /** @var string Código HTML do componente */
    protected $code;
    /** @var array Array de propriedades do componente */
    private $props;

    /**
        Método de construção do componente
     
        @author Marcello Costa

        @package Sagacious\SgsComponent\Codeinjected
     
        @param  array  $props    Array de propriedades
      
        @return  void  Without return
    */
    function __construct(array $props) {
        $this->props=unserialize($props);
        $this->SetAttributes($this->props);
    }
    
    /**
        Define os atributos do componente
     
        @author Marcello Costa

        @package Sagacious\SgsComponent\Codeinjected
     
        @param  array  $props    Array de propriedades
      
        @return  void  Without return
    */
    function SetAttributes(array $props) {
        if (isset($props['code'])) {
          // Montando código HTML
          $this->code=$props['code'];
        }
    }
}
