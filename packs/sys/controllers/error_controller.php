<?php
/**
  Controller de erros do pack sys
*/

// Namespace relativo ao pack do controller
namespace Controllers\sys;

/**
 * Classe responsável por renderizar erros não fatais (como o erro 404)
 * 
 * @author Marcello Costa
 * 
 * @package Controllers\sys\Error_Controller
 * 
 * @Route (path="/error", defaultaction="genericError")
 */
class Error_Controller extends \KeyClass\Controller{
    /**
        Exibe a página de erro genérica
     
        @author Marcello Costa

        @package Controllers\sys\Error_Controller

        @Route (path="genericError")

        @return Void
    */
    public function genericError() {
        $msgError = 'Oops, something strange happened on this page.';
        if (\Helpers\globalHelper::isResponseFormat('JSON') || \Helpers\globalHelper::isResponseFormat('XML')) {
            $errorData = [];
            $errorData['error']=$msgError;
            $this->responseAPI($errorData);
        }
        else {
            $this->AddViewBag($msgError, 'msgError');
            $this->renderView('sys::error/generic.sgv');
        }
    }

    /**
        Renderiza a página de erro para o administrador do sistema
     
        @author Marcello Costa

        @package Controllers\sys\Error_Controller
     
        @return Void
    */
    public function adminMessageError() {
        global $kernelspace;
        $registeredErrors = $kernelspace->getVariable('registeredErrors', 'insiderFrameworkSystem');
        $consoleRequest = $kernelspace->getVariable('consoleRequest', 'insiderFrameworkSystem');
        
        if (\Helpers\globalHelper::existAndIsNotEmpty($registeredErrors, 'messagesToAdmin')){
            // Do not display if is console request. Don`t need this because 
            // "error_log" callfunction in KC_ERROR will display error in 
            // console output automatically without developer intervention.
            if (!$consoleRequest){
                $this->AddViewBag($registeredErrors, 'msgError');
                $this->renderView('sys::error/sys_error_msg.sgv');
            }
        }
    }

    /**
        Exibe a página de erro para alto uso da CPU
     
        @author Marcello Costa

        @package Controllers\sys\Error_Controller
     
        @Route (path="loadAvg")
        @return Void
    */
    public function loadAvg() {
        $this->renderView('sys::error/load_avg.sgv');
    }

    /**
        Renderiza o template "Erro 404 (página não encontrada)"
     
        @author Marcello Costa

        @package Controllers\sys\Error_Controller
     
        @Route (path="notFound")
        @return Void
    */
    public function notFound() {
        global $kernelspace;
        
        $originalUrlRequested = $kernelspace->getVariable('routeObject', 'insiderRoutingSystem')->getOriginalUrlRequested();

        $responseFormat = $kernelspace->getVariable('responseFormat', 'insiderFrameworkSystem');
        if ($responseFormat === 'HTML') {
            $this->addViewBag($originalUrlRequested, 'originalUrlRequested');
            $this->renderView('sys::error/404.sgv');
        }
        else {
            http_response_code(404);
            primaryError('Route not found');
        }
    }
}
?>
