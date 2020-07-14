<?php

namespace Apps\Sys\Controllers;

use Modules\InsiderFramework\Core\Validation\Aggregation;
use Modules\InsiderFramework\Core\Validation\Request;
use Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag;
use Modules\InsiderFramework\Console\ConsoleError;
use Modules\InsiderFramework\Core\Error\ErrorMessage;
use Modules\InsiderFramework\Core\KernelSpace;
use Modules\InsiderFramework\Core\Error\ErrorHandler;

/**
 * Class responsible for rendering non-fatal errors (such as 404 error)
 *
 * @author Marcello Costa
 *
 * @package Apps\Sys\Controllers\ErrorController
 *
 * @Route (path="/error", defaultaction="genericError")
 */
class ErrorController extends \Modules\InsiderFramework\Core\Controller
{
    /**
     * Displays the generic error page
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\ErrorController
     *
     * @Route (path="genericError")
     *
     * @return void
    */
    public function genericError(): void
    {
        $consoleRequest = KernelSpace::getVariable(
            'consoleRequest',
            'insiderFrameworkSystem'
        );

        $genericMsgError = 'Oops, something strange happened on this page.';

        if ($consoleRequest) {
            ConsoleError::errorRegister($genericMsgError);
        }
        
        if (Request::isResponseFormat('JSON') || Request::isResponseFormat('XML')) {
            $errorData = [];
            $errorData['error'] = $genericMsgError;
            $this->responseAPI($errorData);
        } else {
            SgsViewsBag::set('genericMsgError', $genericMsgError);
            $this->renderView('Sys::error/generic.sgv');
        }
    }

    /**
     * Render the error page to the system administrator
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\ErrorController
     *
     * @return void
    */
    public function adminMessageError(): void
    {
        $consoleRequest = KernelSpace::getVariable(
            'consoleRequest',
            'insiderFrameworkSystem'
        );

        $registeredErrors = KernelSpace::getVariable(
            'registeredErrors',
            'insiderFrameworkSystem'
        );

        if (is_array($registeredErrors) && Aggregation::existAndIsNotEmpty($registeredErrors, 'messagesToAdmin')) {
            $error = array_pop($registeredErrors['messagesToAdmin']);
            SgsViewsBag::set('msgError', $error);
        } else {
            SgsViewsBag::set('msgError', "Unknown error");
        }

        if ($consoleRequest) {
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
                ErrorHandler::primaryError('Unknow error: ' . json_encode($msg));
            }
        }
        $this->renderView('Sys::error/adminMessageError.sgv');
    }

    /**
     * Displays the error page for high CPU usage
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\ErrorController
     *
     * @Route (path="loadAvg")
     *
     * @return void
    */
    public function loadAvg(): void
    {
        $this->renderView('Sys::error/loadAvg.sgv');
    }

    /**
     * Render the template "Error 404 (page not found)"
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\ErrorController
     *
     * @Route (path="notFound")
     *
     * @return void
    */
    public function notFound(): void
    {
        $responseFormat = KernelSpace::getVariable(
            'responseFormat',
            'insiderFrameworkSystem'
        );
        if ($responseFormat === 'HTML') {
            $this->renderView('Sys::error/404.sgv');
        } else {
            http_response_code(404);
            ErrorHandler::primaryError('Page or resource not found');
        }
    }

    /**
     * Render the template "NoJavascript"
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\ErrorController
     *
     * @Route (path="nojavascript")
     *
     * @return void
    */
    public function noJavascript(): void
    {
        $responseFormat = KernelSpace::getVariable(
            'responseFormat',
            'insiderFrameworkSystem'
        );
        if ($responseFormat === 'HTML') {
            $this->renderView('Sys::error/noJavascript.sgv');
        } else {
            http_response_code(404);
            ErrorHandler::primaryError('No javascript detected');
        }
    }
}
