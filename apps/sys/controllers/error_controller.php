<?php

namespace Controllers\sys;

use Modules\InsiderFramework\Core\Validation\Aggregation;
use Modules\InsiderFramework\Core\Validation\Request;
use Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag;
use Modules\InsiderFramework\Console\ConsoleError;
use Modules\InsiderFramework\Core\Error\ErrorMessage;

/**
 * Classe responsável por renderizar erros não fatais (como o erro 404)
 *
 * @author Marcello Costa
 *
 * @package Controllers\sys\ErrorController
 *
 * @Route (path="/error", defaultaction="genericError")
 */
class ErrorController extends \Modules\InsiderFramework\Core\Controller
{
    /**
     * Exibe a página de erro genérica
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\ErrorController
     *
     * @Route (path="genericError")
     *
     * @return void
    */
    public function genericError(): void
    {
        $consoleRequest = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'consoleRequest',
            'insiderFrameworkSystem'
        );

        $msgError = 'Oops, something strange happened on this page.';

        if (!is_null($consoleRequest)) {
            ConsoleError::errorRegister($msgError);
        }
        
        if (Request::isResponseFormat('JSON') || Request::isResponseFormat('XML')) {
            $errorData = [];
            $errorData['error'] = $msgError;
            $this->responseAPI($errorData);
        } else {
            SgsViewsBag::set($msgError, 'msgError');
            $this->renderView('sys::error/generic.sgv');
        }
    }

    /**
     * Renderiza a página de erro para o administrador do sistema
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\Error_Controller
     *
     * @return void
    */
    public function adminMessageError(): void
    {
        $consoleRequest = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'consoleRequest',
            'insiderFrameworkSystem'
        );

        $registeredErrors = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'registeredErrors',
            'insiderFrameworkSystem'
        );

        if (is_array($registeredErrors) && Aggregation::existAndIsNotEmpty($registeredErrors, 'messagesToAdmin')) {
            $error = array_pop($registeredErrors['messagesToAdmin']);
            SgsViewsBag::set('msgError', $error);
        } else {
            SgsViewsBag::set('msgError', "Unknown error");
        }

        if (!is_null($consoleRequest)) {
            $msg = SgsViewsBag::get('msgError');
            if (is_array($msg) && isset($msg['jsonMessage'])) {
                $errorArray = json_decode($msg['jsonMessage'], true);
                $error = new ErrorMessage(array(
                    'type' => $errorArray['type'],
                    'text' => $errorArray['message'] ? $errorArray['message'] : $errorArray['text'],
                    'file' => $errorArray['file'],
                    'line' => $errorArray['line'],
                    'fatal' => $errorArray['fatal'],
                    'subject' => $errorArray['subject']
                ));
                ConsoleError::errorRegister($error);
            } else {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError('Unknow error: ' . json_encode($msg));
            }
        }
        $this->renderView('sys::error/sys_error_msg.sgv');
    }

    /**
     * Exibe a página de erro para alto uso da CPU
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\ErrorController
     *
     * @Route (path="loadAvg")
     *
     * @return void
    */
    public function loadAvg(): void
    {
        $this->renderView('sys::error/load_avg.sgv');
    }

    /**
     * Renderiza o template "Erro 404 (página não encontrada)"
     *
     * @author Marcello Costa
     *
     * @package Controllers\sys\ErrorController
     *
     * @Route (path="notFound")
     *
     * @return void
    */
    public function notFound(): void
    {
        $originalUrlRequested = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'routeObject',
            'RoutingSystem'
        )->getOriginalUrlRequested();

        $responseFormat = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'responseFormat',
            'insiderFrameworkSystem'
        );
        if ($responseFormat === 'HTML') {
            \Modules\InsiderFramework\Sagacious\Lib\SgsViewsBag::set($originalUrlRequested, 'originalUrlRequested');
            $this->renderView('sys::error/404.sgv');
        } else {
            http_response_code(404);
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError('Route not found');
        }
    }
}
