<?php
/**
  View (SgsComponent) 
*/
namespace Sagacious;

/**
  Classe principal do componente View (SgsComponent)
  
  @author Marcello Costa
  
  @package Sagacious\SgsComponent\View
*/
class View extends \Sagacious\SgsComponent{
    /** @var string Código HTML do componente */
    protected $code;
    /** @var array Array de propriedades do componente */
    private $props;

    /**
        Método de construção do componente
     
        @author Marcello Costa
      
        @package Sagacious\SgsComponent\View
      
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
      
        @package Sagacious\SgsComponent\View
     
        @param  array  $props    Array de propriedades
      
        @return  void  Without return
    */
    function SetAttributes(array $props) {
        // Inicializando variáveis
        $viewname=null;
        $pathview=null;
        $template=null;
        
        // Capturando propriedades
        foreach ($props as $propk => $propv) {
            switch($propk) {
                case "viewname":
                    $viewname=$propv;
                break;

                case "pathview":
                    $pathview=$propv;
                break;

                case "pack":
                    $pack=$propv;
                break;
            
                case "template":
                    $template=$propv;
                break;

                case "params":
                    $params=$propv;
                break;

                case "ajaxrequest":
                    $ajaxrequest=$propv;
                break;
            
                case "jsonreturn":
                    $jsonreturn=$propv;
                break;
            }
        }

        // Construindo o array de views
        $views=null;
        if ($viewname !== null) {
            $views[$viewname]=array(
                "path" => $pathview
            );
        }

        // Instanciando controller para renderizar a view
        $controller_view = new \KeyClass\Controller($pack, $template, $views, $params, $ajaxrequest);

        // Renderizando view para uma variável
        $this->code=$controller_view->renderViewToString($viewname);
        
        // Instanciando a classe SgsPage utilizadas
        $KcPage=new \Sagacious\SgsPage();
        
        // Se o código retornado é um JSON, o mesmo deve ser transformado
        // em código puro
        if (\KeyClass\JSON::isJSON($this->code)) {
            $viewJSON=json_decode($this->code);

            // Setando o código na variável do componente
            $this->code=$viewJSON->{'code'};

            // Adicionando script e css no código a ser exibido
            $cssView=$viewJSON->{'css'};
            if ($cssView !== "") {
                $KcPage->updateCSSOfPage($cssView);
            }

            $jsView=$viewJSON->{'script'};
            if ($jsView !== "") {
                $KcPage->updateJSOfPage(JsView);    
            }
        }
    }
}
