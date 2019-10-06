<?php

/**
  KeyClass\Error File
 */

namespace KeyClass;

/**
  KeyClass that contains functions for handling errors
 
  @package KeyClass\Error

  @author Marcello Costa
 */
class Error {
    /**
      Function that shows / register translated errors

      @author Marcello Costa

      @package KeyClass\Error

      @param  string  $message             Error message
      @param  string  $domain              Domain of error message
      @param  string  $linguas             Language of error message
      @param  string  $type                Type of error
      @param  int     $responseCode        Response code of error

      @return void
     */
    public static function i10nErrorRegister(string $message, string $domain, string $linguas = LINGUAS, string $type = "CRITICAL", $responseCode = null) : void {
        $msgI10n = \KeyClass\I10n::getTranslate($message, $domain, $linguas);
        \KeyClass\Error::errorRegister($msgI10n, $type, $responseCode);
    }

    /**
      Função que registra/exibe erros

      @author Marcello Costa

      @package KeyClass\Error

      @param  string  $message             Mensagem de erro
      @param  string  $type                Tipo de erro
      @param  int     $responseCode        Código de resposta do erro

      @return void|string Retorna o unidid do erro se for do tipo LOG
     */
    public static function errorRegister(string $message, string $type = "CRITICAL", int $responseCode = null) : ?string {
        global $kernelspace;
        $debugbacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        
        $file = $debugbacktrace[0]['file'];
        $line = $debugbacktrace[0]['line'];
        if (isset($debugbacktrace[2])){
            $file = $debugbacktrace[2]['file'];
            $line = $debugbacktrace[2]['line'];
        }

        switch (strtoupper(trim($type))) {
            // Erro que apenas escreve no arquivo de log
            case "LOG":
                // Gera ID único para este erro
                $id = uniqid();

                // Cria um log novo caso o arquivo já esteja sendo usado
                $logfilepath = INSTALL_DIR . "/frame_src/cache/logs/logfile-" . $id;
                while (file_exists($logfilepath . ".lock")) {
                    $logfilepath = INSTALL_DIR . "/frame_src/cache/logs/logfile-" . $id;
                }

                // Inserindo o formato à mensagem (por enquanto é fixo)
                $date = new \DateTime('NOW');
                $dataFormat = $date->format('Y-m-d H:i:s');
                $message = $dataFormat . "    " . $message;

                // Escrevendo no arquivo de log
                \KeyClass\FileTree::fileWriteContent($logfilepath, $message);

                // Retorna o ID do erro
                return $id;
            break;

            // Erro crítico
            case "CRITICAL":
                if ($responseCode === null) {
                    $responseCode = 500;
                }
                // Resposta HTTP 500
                http_response_code($responseCode);

                // Populando objeto do erro
                $manageErrorMsg = new \Modules\insiderErrorHandler\manageErrorMsg(array(
                    'type' => $type,
                    'text' => $message,
                    'file' => $file,
                    'line' => $line,
                    'fatal' => true,
                    'subject' => 'Critical Error - Report Agent InsiderFramework'
                ));

                // Definindo variável global
                $kernelspace->setVariable(array('fatalError' => true), 'insiderFrameworkSystem');

                // Tratando erro
                \KeyClass\Error::manageError($manageErrorMsg);
            break;

            // Provável ataque ao site
            case "ATTACK_DETECTED":
                if ($responseCode === null) {
                    $responseCode = 405;
                }

                // Resposta HTTP 405
                http_response_code($responseCode);

                // Nomes dos cookies
                // Email User = eu
                $cookie1Name = md5('user_identify_cookie_insider');

                // Cookie IDsession (nome encriptado com outro método)
                $cookie2Name = htmlspecialchars("idsession");

                // Capturando valores dos cookies
                $cookie1Value = \KeyClass\Security::getCookie($cookie1Name);
                $cookie2Value = \KeyClass\Security::getCookie($cookie2Name);

                // Montando mensagem
                $message .= '- Cookies: (1)' . $cookie1Value . ' (2)' . $cookie2Value;

                // Construindo array do erro
                $error = new \Modules\insiderErrorHandler\manageErrorMsg(array(
                    'type' => $type,
                    'text' => $message,
                    'file' => $file,
                    'line' => $line,
                    'fatal' => true,
                    'subject' => 'Attack Error - Report Agent InsiderFramework'
                ));

                // Definindo variável global
                $kernelspace->setVariable(array('fatalError' => true), 'insiderFrameworkSystem');


                // Tratando erro
                \KeyClass\Error::manageError($error);
            break;

            // Este é um tipo de erro que irá retornar um JSON para
            // o usuário (útil em requisições ajax, por exemplo)
            case 'JSON_PRE_CONDITION_FAILED':
                if ($responseCode === null) {
                    $responseCode = 412;
                }

                // Definindo como 412 Precondition Failed
                http_response_code($responseCode);

                // Construindo array do erro
                $error = array(
                    'error' => $message
                );

                echo \KeyClass\JSON::jsonEncodePrivateObject($error);
                exit();
            break;

            // Este é um tipo de erro que irá retornar um XML para
            // o usuário (útil em requisições ajax, por exemplo)
            case 'XML_PRE_CONDITION_FAILED':
                if ($responseCode === null) {
                    $responseCode = 412;
                }

                // Definindo como 412 Precondition Failed
                http_response_code($responseCode);

                // Construindo array do erro
                $error = array(
                    'error' => $message
                );

                if (!\KeyClass\XML::isXML($error)) {
                    $xmlObj = "";
                    $message = \KeyClass\XML::arrayToXML($error, $xmlObj);
                    if ($message === false) {
                        primaryError("Unable to convert error to XML when triggering error");
                    }
                    echo $message;
                }
                exit();
            break;

            // Erro comum (ou "STANDARD")
            default:
                if ($responseCode !== null) {
                    http_response_code($responseCode);
                }

                // Construindo array do erro
                $error = new \Modules\insiderErrorHandler\manageErrorMsg(array(
                    'type' => $type,
                    'text' => $message,
                    'file' => $file,
                    'line' => $line,
                    'fatal' => false,
                    'subject' => 'Standard Error - Report Agent InsiderFramework'
                ));

                // Definindo variável global
                $kernelspace->setVariable(array('fatalError' => false), 'insiderFrameworkSystem');

                // Tratando erro
                \KeyClass\Error::manageError($error);
            break;
        }
    }

    /**
      Função que recupera o debug atual

      @author Marcello Costa

      @package KeyClass\Error

      @return bool Estado do debug atual
     */
    public static function getFrameworkDebugStatus() : bool {
        global $kernelspace;

        $contentConfig = $kernelspace->getVariable('contentConfig', 'insiderFrameworkSystem');
        
        // Se o DEBUG não está definido, as variáveis de ambiente devem ser carregadas manualmente
        // O path provavelmente não será mapeado corretamente com getcwd(), então a constante __DIR__
        // é recuperada e tratada de acordo
        $path = __DIR__;
        $path = explode(DIRECTORY_SEPARATOR, $path);
        if (count($path) === 0) {
            primaryError("Unable to recover installation directory when trigger error");
        }

        try {
            $path = implode(array_slice($path, 0, count($path) - 3), DIRECTORY_SEPARATOR);
        } catch (\Exception $e) {
            primaryError("Unable to rebuild installation directory when trigger error");
        }

        $coreEnvFile = $path . DIRECTORY_SEPARATOR . 'frame_src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'core.json';

        if (!file_exists($coreEnvFile)) {
            primaryError("Env file does not exist when triggering error");
        }
        $contentConfig = json_decode(file_get_contents($coreEnvFile));
        if ($contentConfig === null) {
            primaryError("Specific core file does not contain environment information when triggering error");
        }
        if (!property_exists($contentConfig, 'DEBUG')) {
            primaryError("Unable to set DEBUG state when trigger error");
        }
        $kernelspace->setVariable(array('contentConfig' => $contentConfig), 'insiderFrameworkSystem');
        
        // Definindo o debug manualmente para uma variável
        $debugNow = $contentConfig->DEBUG;

        return $debugNow;
    }

    /**
      Function that manage an error

      @author Marcello Costa

      @package KeyClass\Error

      @param  \Modules\insiderErrorHandler\manageErrorMsg  $error    Object with error information

      @return void
     */
    public static function manageError(\Modules\insiderErrorHandler\manageErrorMsg $error) : void {
        // Inicializando a variável de erro (se ainda não existe)
        global $kernelspace;
       
        // Registered errors
        $registeredErrors = $kernelspace->getVariable('registeredErrors', 'insiderFrameworkSystem');
        if (!is_array($registeredErrors))
        {
            $registeredErrors = [];
            $kernelspace->setVariable(array('registeredErrors' => $registeredErrors));
        }
        // A primeira coisa a fazer é gravar o erro no log do webserver
        error_log(\KeyClass\JSON::jsonEncodePrivateObject($error), 0);

        $responseFormat = $kernelspace->getVariable('responseFormat', 'insiderFrameworkSystem');
        if ($responseFormat === "") {
          $responseFormat = DEFAULT_RESPONSE_FORMAT;
          $kernelspace->setVariable(array('responseFormat' => $responseFormat), 'insiderFrameworkSystem');
        }
        
        // Esta primeira parte é exibida se o processamento não for bem sucedido
        // nas próximas linhas
        clearAndRestartBuffer();
        
        // Gravando a mensagem padrão para o usuário
        $defaultMsg = 'Oops, something is wrong with this URL. See the error_log for details';
        if (!isset($registeredErrors['messageToUser']) || !in_array($defaultMsg, $registeredErrors['messageToUser'])){
            $registeredErrors['messageToUser'][]=$defaultMsg;
            $kernelspace->setVariable(array('registeredErrors' => $registeredErrors), 'insiderFrameworkSystem');
        }

        // Recuperando variável de erro fatal
        $fatal = $kernelspace->getVariable('fatalError', 'insiderFrameworkSystem');
        
        // Recuperando contador de erros
        $errorCount = $kernelspace->getVariable('errorCount', 'insiderFrameworkSystem');
        
        $debugbacktrace = $kernelspace->getVariable('debugbacktrace', 'insiderFrameworkSystem');

        // In here the framework checks if this piece of code already been executed
        // with some fatal error. If so, they will display a message with the error
        // directly for the user and write a log with the detais.
        if ($debugbacktrace === null){
            $debugbacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50);
            $kernelspace->setVariable(array('debugbacktrace' => $debugbacktrace), 'insiderFrameworkSystem');
        }

        // Se não houve um erro anterior
        if ($errorCount === null){
            $errorCount = 0;
        }
        else{
            $errorCount++;
        }
        $kernelspace->setVariable(array('errorCount' => $errorCount), 'insiderFrameworkSystem');

        // Se mais de 10 erros foram mapeados
        if ($errorCount > 10){
            $finalErrorMsg = "Max log errors on framework";
            
            // Definindo o response code como 500
            http_response_code(500);

            // Gravando os detalhes do erro no log
            error_log(json_encode($debugbacktrace));

            // Se o debug não está ativado
            if (!DEBUG){
                // Parando a execução com a mensagem default
                clearAndRestartBuffer();
                primaryError($finalErrorMsg);
            }
            // Se o debug está ativado
            else{
                // Parando a execução e exibindo o objeto de erro
                clearAndRestartBuffer();
                primaryError($finalErrorMsg);
            }
        }

        // Tratando o path do erro (para mostrar o caminho relativo)
        $path = __DIR__;
        $path = explode(DIRECTORY_SEPARATOR, $path);
        if (count($path) === 0) {
            clearAndRestartBuffer();
            primaryError("Unable to recover installation directory when trigger error");
        }

        try {
            $relativePath = implode(array_slice($path, 0, count($path) - 3), DIRECTORY_SEPARATOR);
        } catch (\Exception $e) {
            clearAndRestartBuffer();
            primaryError("Unable to translate the relative installation directory when triggering error");
        }

        // If DEBUG is not defined, is some error inside the framework
        if (DEBUG === null) {
            define('DEBUG', \KeyClass\Error::getFrameworkDebugStatus());
        }

        // Data of error (for admin)
        $msgToAdmin = array(
            'jsonMessage' => \KeyClass\JSON::jsonEncodePrivateObject($error),
            'errfile' => str_replace($relativePath, "", $error->getFile()),
            'errline' => $error->getLine(),
            'msgError' => str_replace($relativePath, "", $error->getMessageOrText())
        );

        // Registrando erro no kernelspace (para acesso posterior da view)
        if (!isset($registeredErrors['messagesToAdmin']) || !array_key_exists($msgToAdmin['jsonMessage'], $registeredErrors['messagesToAdmin'])){
            $registeredErrors['messagesToAdmin'][$msgToAdmin['jsonMessage']]=$msgToAdmin;
            $kernelspace->setVariable(array('registeredErrors' => $registeredErrors), 'insiderFrameworkSystem');
        }
        
        // Se o DEBUG está ativo
        if (DEBUG){                        
            switch ($responseFormat) {
                case 'XML':
                    $xml = new \SimpleXMLElement('<error/>');
                    unset($msgToAdmin['jsonMessage']);
                    
                    // Invertendo chaves e valores do array
                    $msgToAdmin=array_flip($msgToAdmin);
                    array_walk_recursive($msgToAdmin, array($xml, 'addChild'));
                    clearAndRestartBuffer();

                    // Todos os erros em XML devem ser exibidos apenas
                    // eles (sem outras interferências) caso contrário
                    // o XML não será válido
                    exit($xml->asXML());
                break;

                case 'JSON':
                    $msgError = array(
                        'error' => json_decode($msgToAdmin['jsonMessage'])
                    );
                        
                    clearAndRestartBuffer();

                    // Todos os erros em JSON devem ser exibidos apenas
                    // eles (sem outras interferências) caso contrário
                    // o JSON não será válido
                    exit(json_encode($msgError));
                break;

                default:
                    // Recuperando a mensagem para o admin formatada
                    \KeyClass\FileTree::requireOnceFile(INSTALL_DIR . DIRECTORY_SEPARATOR . 'packs' . DIRECTORY_SEPARATOR . 'sys' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'error_controller.php');
                    $C = new \Controllers\sys\Error_Controller('\\Controllers\\sys\\sys', null, false);
                    
                    $registeredErrors = $kernelspace->getVariable('registeredErrors', 'insiderFrameworkSystem');

                    $C->adminMessageError();
                break;
            }
        }
        // Se o DEBUG não está ativo
        else{
            // De acordo com a política de envio de email
            $contentConfig = $kernelspace->getVariable('contentConfig', 'insiderFrameworkSystem');
            if ($contentConfig === null){
                \KeyClass\Error::getFrameworkDebugStatus();
                $contentConfig = $kernelspace->getVariable('contentConfig', 'insiderFrameworkSystem');
            }
            
            if (!property_exists($contentConfig, 'ERROR_MAIL_SENDING_POLICY')) {
                clearAndRestartBuffer();
                primaryError("Unable to read email sending policy when trigger error");
            }

            switch (strtolower(trim($contentConfig->ERROR_MAIL_SENDING_POLICY))) {
                case "debug-off-only":
                    // Enviando o e-mail
                    // Se não conseguir enviar um email para o email default
                    if (!(\KeyClass\Mail::sendMail(MAILBOX, MAILBOX, MAILBOX_PASS, $error['subject'], $htmlMessageToAdmin, $htmlMessageToAdmin, MAILBOX_SMTP, MAILBOX_SMTP_PORT, MAILBOX_SMTP_AUTH, MAILBOX_SMTP_SECURE))) {
                        clearAndRestartBuffer();
                        // Envia uma mensagem para o log do servidor web
                        primaryError("Unable to send an error message via email to the default mailbox when triggering an error!");
                    }
                break;

                case "never":
                break;

                default:
                    clearAndRestartBuffer();
                    $msg = 'Email sending policy \'' . $contentConfig->ERROR_MAIL_SENDING_POLICY . '\' not identified when trigger error';
                    error_log($msg);
                    primaryError($msg);
                break;
            }

            // Mostrando a mensagem default de erro
            \KeyClass\FileTree::requireOnceFile(INSTALL_DIR . DIRECTORY_SEPARATOR . 'packs' . DIRECTORY_SEPARATOR . 'sys' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'error_controller.php');
            $C = new \Controllers\sys\Error_Controller('\\Controllers\\sys\\sys', null, false);
            clearAndRestartBuffer();
            $C->genericError();
        }

        // Matando o processamento se o erro for fatal
        if ((isset($fatal) && $fatal === true) || $error->getFatal() === true) {
            exit();
        }
    }

    /**
      Quando a classe não é encontrada, gera uma excecão exibida por esta função

      @author Marcello Costa

      @package KeyClass\Error

      @param  string  $class    Nome da classe
      @param  string  $file     Nome do arquivo da classe

      @return void  Without return
     */
    public static function classNotFound(string $class, string $file) : void {
        primaryError("Class " . $class . " not found in file " . $file . " !");
    }

    /**
      Quando o arquivo da classe não é encontrado, gera uma excecão exibida
      por esta função

      @author Marcello Costa

      @package KeyClass\Error

      @param  string  $file           Nome do arquivo da classe
      @param  string  $soughtclass    Nome da classe que requisitou o arquivo
      @param  string  $namespace      Nome do namespace

      @return void  Without return
     */
    public static function classFileNotFound(string $file, string $soughtclass, string $namespace = null) : void {
        if ($namespace !== null) {
            $text = "'" . $file . "' of class '" . $soughtclass . "' that belongs to the namespace '" . $namespace . "'";
        } else {
            $text = "'" . $file . "' of class '" . $soughtclass . "' (without declared namespace)";
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        
        primaryError("The file " . $text . " was not found ! Details: ".json_encode($backtrace));
    }
}
