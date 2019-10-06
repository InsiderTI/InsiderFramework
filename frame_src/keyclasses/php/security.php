<?php
/**
  Arquivo KeyClass\Security
*/

// Namespace das KeyClass
namespace KeyClass;

use KeyClass\Model;
use ioncube\phpOpensslCryptor\Cryptor;

/**
   KeyClass que contém funções de segurança e sessão

   @package KeyClass\Security

   @author Marcello Costa
*/
class Security{
    /**
        Função que retorna o nível acesso do usuário para uma rota
      
        @author Marcello Costa

        @package KeyClass\Security
      
        @param  routeData  $routeObj      Objeto da rota atual

        @return  mixed  Retorna o nível de acesso.
    */
    public static function getUserAccessLevel(\Modules\insiderRoutingSystem\routeData $routeObj) {
        global $kernelspace;
        $ajaxrequest = $kernelspace->getVariable('ajaxrequest', 'insiderRoutingSystem');
        $permissions = $routeObj->getPermissions();
        
        // Se o arquivo de controller de segurança existe
        if (file_exists(INSTALL_DIR.DIRECTORY_SEPARATOR.'packs'.DIRECTORY_SEPARATOR.'sys'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'security_controller.php')) {
            switch (strtolower($permissions['type'])){
                // Se a checagem de segurança de acesso do framework está habilitada
                case 'native':
                    return \Modules\insiderRoutingSystem\Permission::getNativeAccessLevel();
                break;
                // Se a checagem de segurança de acesso customizada está habilitada
                case 'custom':
                    \KeyClass\FileTree::requireOnceFile(INSTALL_DIR.DIRECTORY_SEPARATOR.'packs'.DIRECTORY_SEPARATOR.'sys'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'security_controller.php');
                    $SecurityController = new \Controllers\sys\Security_Controller('sys', null, $ajaxrequest);

                    // Retornando resultado da verificação
                    return $SecurityController->getCustomAccessLevel();
                break;
                default:
                    primaryError('ACL_METHOD not recognized');
                break;
            }
        }
        // Se o arquivo do controller não existe
        else {
            \KeyClass\Error::i10nErrorRegister("Security controller not found for user privilege verification", 'pack/sys');
        }
    }

    /**
        Destrói a sessão php de forma plena
     
        @author Marcello Costa

        @package KeyClass\Security

        @return void Without return
    */
    public static function destroySession() : void {
        $sessionParams = session_get_cookie_params();
        
        // Se é um array
        if (is_array($sessionParams)) {
            // Setando o cookie de sessão
            \KeyClass\Security::setCookie(
                session_name(), 
                '', 
                $sessionParams['path'], 
                time()-36000, 
                $sessionParams['domain'], 
                $sessionParams['secure'], 
                $sessionParams['httponly']
            );
        }
        
        // Destruindo a sessão
        session_destroy();
    }

    /**
        Verifica se um cookie existe
     
        @author Marcello Costa

        @package KeyClass\Security
     
        @param  string  $cookieName      Nome do cookie
        @param  string|int|bool  $value   Valor esperado do cookie
     
        @return  bool  Retorno booleano
    */
    public static function checkCookie(string $cookieName, $value=NULL) : bool {
        // Se o cookie existir
        if (isset($_COOKIE[$cookieName])) {
            // Se for esperado algum valor específico no cookie
            if ($value !== NULL) {
                if (\KeyClass\Security::getRequest('cookie')[$cookieName] !== $value) {
                    return false;
                }
                else {
                    return true;
                }
            }

            // Se não for esperado algum valor específico no cookie
            // ou o valor for o especificado
            else {
                return true;
            }

        }

        // Cookie não existe
        return false;
    }

    /**
        Função que cria um cookie
     
        @author Marcello Costa

        @package KeyClass\Security
     
        @param  string  $name           Nome do cookie
        @param  string  $cookievalue    Valor do cookie
        @param  string  $path           Caminho de acesso do cookie (url). Se não for
                                        definido qual é a região do cookie, ele então
                                        serve para o site inteiro.
        @param  string  $expiretime     Validade em minutos do cookie.
        @param  string  $domain         Domínio (url) do cookie.
        @param  bool    $https          Indica que o cookie só podera ser transimitido sob
                                        uma conexão segura (HTTPS) do cliente . Se não foi
                                        especificado que é https, então desativa esta opção.
        @param  bool    $htmlOnly       Quando for TRUE o cookie será acessível somente
                                        sob o protocolo HTTP. Isso significa que o
                                        cookie não será acessível por linguagens de
                                        script, como JavaScript. Valor padrão: false;
     
        @return void Without return
    */
    public static function setCookie(string $name, string $cookievalue=null, string $path=null, string $expiretime=null, string $domain=null, bool $https=false, bool $htmlOnly=false) : void {
        // Se não existir um value, é uma chave encriptada + time + idúnico
        if ($cookievalue == null) {
            $cookievalue=time().uniqid();

            // Pega o IP do usuário (não funciona localmente)
            $ipuser=getenv("REMOTE_ADDR");

            // Usando a senha abaixo, gera uma chave e armazena na variável $hash
            $cookievalue = \KeyClass\Security::encryptString($cookievalue.$ipuser);
        }

        // Se não existir um path, pega a home
        if ($path == null) {
            $path="/";
        }

        // Se não houver tempo para expirar, coloca a validade para 24 horas
        if ($expiretime == null) {
            $expiretime=time()+3600*24;
        }

        // Se não for especifcado o domínio, pega o domínio padrão
        if ($domain == null) {
            $domain=str_replace("http://","",REQUESTED_URL);
            $domain=str_replace("https://","",$domain);
        }

        // Cria o cookie
        setcookie($name, $cookievalue, $expiretime, $path, $domain, $https, $htmlOnly);
    }

    /**
        Função alias para unset dos cookies

        @author Marcello Costa

        @package KeyClass\Security
     
        @param  string  $name    Nome do cookie
     
        @return void Without return
    */
    public static function RemoveCookie(string $name) : void {
        if (isset(\KeyClass\Security::getRequest('cookie')[$name])) {
            unset($_COOKIE[$name]);
        }
    }

    /**
        Função que captura e trata o valor de um cookie com a função addslashes
        (nativa do php)
     
        @author Marcello Costa

        @package KeyClass\Security
     
        @param  string  $name    Nome identificador de cookie
     
        @return  string  Valor do cookie tratado
    */
    public static function getCookie(string $name) : ?string {
        // Se o cookie existir
        if (\KeyClass\Security::checkCookie($name)) {
            // Retornando o valor do cookie
            $cookieValue = \KeyClass\Security::getRequest('cookie')[$name];
            return (htmlspecialchars(addslashes($cookieValue)));
        }

        // Se o cookie não existir
        else {
            return null;
        }
    }

    /**
        Encripta uma string

        @author Marcello Costa

        @package KeyClass\Security
     
        @param  string  $string    String a ser codificada
        @param  string  $key       Chave de encriptação
        @param  bool    $md5       Retorno em md5
     
        @return  string  String codificada
    */
    public static function encryptString(string $string, string $key = null, bool $md5=false) : string {
        if ($string !== NULL) {
            // Se a chave de encriptação não foi especificada
            if ($key === null) {
                // Usa a chave definida globalmente
                $key = ENCRYPT_KEY;
            }
            $encrypted = Cryptor::Encrypt($string, $key);

            if ($md5 === false) {
                return $encrypted;
            }
            else {
                return md5($encrypted);
            }
        }
        else {
            primaryError('String for encryption has not been specified');
        }
    }

    /**
        Decripta uma string

        @author Marcello Costa

        @package KeyClass\Security

        @param  string  $string    String codificada
        @param  string  $key       Chave de decriptação
     
        @return  string  String decodificada
    */
    public static function decryptString(string $string, string $key = null) : string {
        if ($string !== NULL) {
            // Se a chave de encriptação não foi especificada
            if ($key === null) {
                // Usa a chave definida globalmente
                $key = ENCRYPT_KEY;
            }
            $decrypted = Cryptor::Decrypt($string, $key);

            return $decrypted;
        }
        else {
            throw new \Exception('String for decryption has not been specified');
        }
    }

    /**
        Seta na variável global informações de post
      
        @author Marcello Costa
     
        @package KeyClass\Security
     
        @param  array  $post       Conteúdo do post
        @param  bool   $overwrite  Sobreescrever ou não
     
        @return void Without return
    */
    public static function setPost(array $post, bool $overwrite=true) : void {
        // Sobreescrever informações existentes
        if ($overwrite) {
            // Apaga todas as informações de post
            \KeyClass\Security::clearPost();

            // Insere as novas informações na variável global
            $_REQUEST['POST']=$post;
        }

        // Não sobreescrever informações existentes
        else {
            if (is_array($post)) {
                $_REQUEST['POST'] = array_merge(\KeyClass\Security::getPost(), $post);
            }
            else {
                \KeyClass\Error::i10nErrorRegister("Error inserting information in post: variable %".$post."% not a valid array!", 'pack/sys');
            }
        }
    }

    /**
        Apaga todas as informações de post da variável global
     
        @author Marcello Costa
     
        @package KeyClass\Security
     
        @return void Without return
    */
    public static function clearPost() : void {
        $_REQUEST['POST'] = null;
    }

    /**
        Função que pega informações de post do array global de request
     
        @author Marcello Costa
     
        @package KeyClass\Security
     
        @return array Informações do post
    */
    public static function getPost() : ?array {
        return (\KeyClass\Security::getRequest('post'));
    }

    /**
        Função que pega informações de get do array global e request
     
        @author Marcello Costa
     
        @package KeyClass\Security
     
        @return array Informações do get
    */
    public static function getGet() : ?array {
        return (\KeyClass\Security::getRequest('get'));
    }

    /**
        Recupera informações filtradas da variável request
     
        @author Marcello Costa
     
        @package KeyClass\Security
     
        @param  string  $type    O que será retornado do array da requisição
     
        @return  array  Dados retornados da request
    */
    public static function getRequest(string $type) : ?array {
        switch(strtolower($type)) {
            case "get":
                return filter_input_array(INPUT_GET);
            break;

            case "post":
                return filter_input_array(INPUT_POST);
            break;

            case "cookie":
                return filter_input_array(INPUT_COOKIE);
            break;

            case "server":
                return filter_input_array(INPUT_SERVER);
            break;

            case "env":
                return filter_input_array(INPUT_ENV);
            break;

            case "session":
                // Ainda não implementado pelo PHP
                //return filter_input_array(INPUT_SESSION);
                if (isset($_SESSION)) {
                    return $_SESSION;
                }
                else {
                    return [];
                }
            break;

            case "request":
                // Ainda não implementado pelo PHP
                // return filter_input_array(INPUT_REQUEST);
                return $_REQUEST;
            break;

            default:
                return false;
            break;
        }
    }
}
