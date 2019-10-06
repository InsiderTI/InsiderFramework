<?php
/**
  ViewBag (SgsComponent) 
*/
namespace Sagacious;

/**
  Classe principal do componente ViewBag (SgsComponent)
  
  @author Marcello Costa
  
  @package Sagacious\SgsComponent\ViewBag
*/
class ViewBag extends \Sagacious\SgsComponent{
    /** @var string Código HTML do componente */
    protected $code;
    /** @var array Array de propriedades do componente */
    private $props;

    /**
        Método de construção do componente
     
        @author Marcello Costa
      
        @package Sagacious\SgsComponent\ViewBag
      
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
      
        @package Sagacious\SgsComponent\ViewBag
     
        @param  array  $props    Array de propriedades
      
        @return  void  Without return
    */
    function SetAttributes(array $props) {
        global $kernelspace;
        $viewBag = $kernelspace->getVariable('viewBag', 'insiderFrameworkSystem');

        $this->code=$viewBag[$props['field']];
    }
}
