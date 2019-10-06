<?php
/**
  Arquivo KeyClass\Debug
*/

// Namespace das KeyClass
namespace KeyClass;

/**
  KeyClass de debug do código

  @package KeyClass\Debug

  @author Marcello Costa
*/
class Debug{
    /**
        Função que exibe a calculadora de renderização
     
        @author Marcello Costa

        @package KeyClass\Debug

        @param  string  $action    Ação a ser tomada ("count" ou "render")
     
        @return void
    */
    public function debugBar(string $action) : void {
        global $kernelspace;
        
        // Requerendo arquivo (manualmente)
        require_once(INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."keyclasses".DIRECTORY_SEPARATOR."php".DIRECTORY_SEPARATOR."security.php");

        switch($action) {
            // Se for o início da contagem da renderização
            case "count":
                // Inicializa cookie que marca o início do script
                // (se já não foi inicializado)
                $startTest=\KeyClass\Security::getCookie('starttime');
                if ($startTest == '0' || $startTest === false) {
                    \KeyClass\Security::setCookie('starttime', microtime());
                }
            break;

            // Parando o contador e renderizando
            case "render":
                // Recuperando informações do tempo da requisição
                $starttime=floatval(\KeyClass\Security::getCookie('starttime'));

                // Inicializa a variável que marca o fim do script
                $end = floatval(microtime());

                // Resetando cookie
                \KeyClass\Security::setCookie('starttime', 0);

                // Calculando quando tempo se passou e o uso de memória
                $elapsedTime = round(floatval($end) - floatval($starttime), 2);
                $memoryUsage = round(((memory_get_peak_usage(true) / 1024) / 1024), 2);

                // Talvez não tenha dado tempo para o navegador enviar o cookie
                if ($elapsedTime < 0) {
                  // Calculando quando tempo se passou e o uso de memória com o start em 0
                  $elapsedTime = round(floatval($end) - floatval(0), 2);
                  $memoryUsage = round(((memory_get_peak_usage(true) / 1024) / 1024), 2);
                }

                // Exibimos uma mensagem na página
                $msg='<div id="debugbarinsiderframe">';                    
                $msg.='<img src="'.REQUESTED_URL.'/favicon.png" id="debugbarinsiderframeimg"/> Processing time: <span style="color:#2C89A0;">'.$elapsedTime.'</span>s / Memory Usage: <span style="color:#FF0000;">'.$memoryUsage.'Mb</span>';
                $msg.='</div>';
                
                $kernelspace->setVariable(array(
                   'injectedHtml' => $kernelspace->getVariable('injectedHtml', 'insiderFrameworkSystem').$msg
                ), 'insiderFrameworkSystem');
             break;
        }
    }
}
