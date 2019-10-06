<?php
// Namespace relativo ao pack do controller
namespace Controllers\start;
use Helpers\globalHelper;

/**
 * @Route (path="/login", defaultaction="loginpage")
 */
class Login_Controller extends \KeyClass\Controller {
    /**
     *  @author Marcello Costa
     *
     *  Método que renderiza a página de login ou informa da necessidade de login
     *
     *  @Route (path="loginPage")
     */
    public function loginPage() {
        global $kernelspace;
        http_response_code(401);

        if (\Helpers\globalHelper::isResponseFormat("JSON") || 
            \Helpers\globalHelper::isResponseFormat("XML") || 
            \Helpers\globalHelper::isAjaxRequest()) {
            if (\Helpers\globalHelper::isResponseFormat("XML")) {
                $this->responseXML('Authentication needed');
            }
            else {
                $this->responseJSON('Authentication needed');
            }
        }
        else {
            $this->renderView('start::login.sgv');
        }
    }
    
    /**
     *  @author Marcello Costa
     *
     *  Método que loga um usuário
     *
     *  @Route (path="loginAction")
     */
    public function loginAction() {
        $this->responseJSON('Not implemented');
    }
}
?>
