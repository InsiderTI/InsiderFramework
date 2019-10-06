<?php
/**
  Title (SgsComponent) 
*/
namespace Sagacious\SgsComponent;

/**
  Classe principal do objeto Title (SgsComponent)
  
  @author Marcello Costa
  
  @package Sagacious\SgsComponent\Title
*/
class Title extends \Sagacious\SgsComponent{
    /** @var string Código HTML do componente */
    protected $code;
    /** @var array Array de propriedades do componente */
    private $props;

    /**
        Método de construção do componente
     
        @author Marcello Costa

        @package Sagacious\SgsComponent\Title
     
        @param  string  $props    String de propriedades
      
        @return  void  Without return
    */
    function __construct(string $props) {
        $this->props=unserialize($props);
        $this->SetAttributes($this->props);
    }

    /**
        Define os atributos do componente
     
        @author Marcello Costa

        @package Sagacious\SgsComponent\Title
     
        @param  array  $props    Array de propriedades
      
        @return  void  Without return
    */
    function SetAttributes(array $props) {
        foreach($props as $p => $pval) {
            switch($p) {
                case 'fixedtitle':
                    $fixedtitle=$pval;
                break;
            
                case 'title':
                    $title=$pval;
                break;
            }
        }

        // Definindo variável que conterá todo o título
        if (!(isset($title))) {
            $titleall=$fixedtitle;
        }

        else {
            $titleall=$fixedtitle." - ".$title;
        }

        // Atualização dinâmica para o título
        $js="<script>
                document.title = '".$titleall."';
             </script>";
        $KcPage = new \Sagacious\SgsPage();
        $KcPage->updateJSOfPage($js);

        // Montando código HTML
        $this->code="<title>".$titleall."</title>";
    }
}
