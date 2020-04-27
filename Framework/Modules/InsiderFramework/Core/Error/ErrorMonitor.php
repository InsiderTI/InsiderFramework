<?php

namespace Modules\InsiderFramework\Core\Error;

/**
 * Monitor e redirecionador de erros. A função ErrorHandler atualmente tem como
 * única função inicializar a checagem de erros no código.
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Error
 */
class ErrorMonitor
{
    /**
    * Initialize the error handler function
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error
    *
    * @return void
    */
    public static function initialize(): void
    {
        register_shutdown_function([
            "\Modules\InsiderFramework\Core\Error\ErrorMonitor",
            "errorHandler"
        ]);
    }

    /**
     * Função que trata todos os possíveis erros que ocorrem na execução
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error
     *
     * @return void
     */
    public static function errorHandler(): void
    {
        // Tipos de erros PHP
        /*

        Fatais:
        1       E_ERROR (integer)
        4       E_PARSE (integer)
        16      E_CORE_ERROR (integer)
        64      E_COMPILE_ERROR
        4096    E_RECOVERABLE_ERROR

        Não fatais:
        2       E_WARNING (integer)
        8       E_NOTICE (integer)
        32      E_CORE_WARNING
        128     E_COMPILE_WARNING
        256     E_USER_ERROR
        512     E_USER_WARNING
        1024    E_USER_NOTICE
        2048    E_STRICT
        8192    E_DEPRECATED
        16384   E_USER_DEPRECATED
        32767   E_ALL

        */
        // Capturando o último erro
        $error = error_get_last();

        // Se existir um erro
        if ($error !== null && error_reporting() !== 0) {
            // Tipo de erro atual
            $errno = $error["type"];
            $message = $error["message"];
            $file = $error["file"];
            $line = $error["line"];

            // Inicializa o flag de erro fatal
            $fatal = \Modules\InsiderFramework\Core\KernelSpace::getVariable('fatalError', 'insiderFrameworkSystem');

            if ($fatal === null) {
                $fatal = false;
            }

            // Limpando o que foi enviado para o output até então
            \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();

            switch ($errno) {
                case 1:
                    $errorType = 'E_ERROR';
                    $fatal = true;
                    break;

                case 2:
                    $errorType = 'E_WARNING';
                    break;

                case 4:
                    $errorType = 'E_PARSE';
                    $fatal = true;
                    break;

                case 8:
                    $errorType = 'E_NOTICE';
                    break;

                case 16:
                    $errorType = 'E_CORE_ERROR';
                    $fatal = true;
                    break;

                case 32:
                    $errorType = 'E_CORE_WARNING';
                    break;

                case 64:
                    $errorType = 'E_COMPILE_ERROR';
                    $fatal = true;
                    break;

                case 128:
                    $errorType = 'E_COMPILE_WARNING';
                    break;

                case 256:
                    $errorType = 'E_USER_ERROR';
                    break;

                case 512:
                    $errorType = 'E_USER_WARNING';
                    break;

                case 1024:
                    $errorType = 'E_USER_NOTICE';
                    break;

                case 2048:
                    $errorType = 'E_STRICT';
                    break;

                case 4096:
                    $errorType = 'E_RECOVERABLE_ERROR';
                    $fatal = true;
                    break;

                case 8192:
                    $errorType = 'E_DEPRECATED';
                    break;

                case 16384:
                    $errorType = 'E_USER_DEPRECATED';
                    break;
            }

            // Definindo tipo de erro (fatal ou não) no array
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                array(
                    'fatalError' => $fatal
                ),
                'insiderFrameworkSystem'
            );
            $error['fatal'] = $fatal;

            // Definindo tipo de erro
            $error['type'] = $errorType;

            // Definindo o assunto da mensagem
            if ($fatal == true) {
                $subject = "Fatal Error - Report Agent InsiderFramework";
            } else {
                $subject = "Warning Error - Report Agent InsiderFramework";
            }

            // Assunto do email
            $error['subject'] = $subject;

            // Tipo de resposta
            $responseFormat = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'responseFormat',
                'insiderFrameworkSystem'
            );
            if ($responseFormat === "") {
                $responseFormat = DEFAULT_RESPONSE_FORMAT;
            } else {
                $responseFormat = $responseFormat;
            }

            // Enviando e exibindo mensagem
            $ErrorMessage = new \Modules\InsiderFramework\Core\Error\ErrorMessage(array(
                'type' => $errorType,
                'text' => $message,
                'file' => $file,
                'line' => $line,
                'fatal' => true,
                'subject' => 'Critical Error - Report Agent InsiderFramework'
            ));

            \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                array(
                    'ErrorMessage' => $ErrorMessage
                ),
                'insiderFrameworkSystem'
            );
            \Modules\InsiderFramework\Core\Error\ErrorHandler::manageError($ErrorMessage);
        }
    }
}
