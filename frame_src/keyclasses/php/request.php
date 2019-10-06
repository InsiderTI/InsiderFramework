<?php
/**
  Arquivo KeyClass\Request
*/

// Namespace das KeyClass
namespace KeyClass;

/**
   KeyClass responsável por executar as requisições de ações em 
   Models e Controllers

   @package KeyClass\Request

   @author Marcello Costa
*/
class Request{
    /**
        Requisição de ação com controller
     
        @author Marcello Costa

        @package KeyClass\Request
      
        @param  string $controller        Nome do controller (com o pack)
        @param  array  $params            Array de parâmetros que foram recebidos na request
     
        @return object Objeto Controller
    */
    public static function Controller(string $controller, array $params=null) : \KeyClass\Controller {

        if (strpos($controller, "::") === false) {
            // Tentando pegar o pack via explode
            $dataExp = explode("\\",$controller);
            if (count($dataExp) !== 2) {
              \KeyClass\Error::i10nErrorRegister("The statement appears to be incorrect when requesting the controller %".$controller."%", 'pack/sys');
            }
            $pack = $dataExp[0];
            $controller = $dataExp[1];

            // Construindo o nome do controller
            $fullControllerName="\\Controllers\\".$dataExp[0]."\\".$dataExp[1]."_Controller";
        }
        else {
            $dataExp = explode('::',$controller);
            if (count($dataExp) !== 2) {
              \KeyClass\Error::i10nErrorRegister("The statement appears to be incorrect when requesting the controller %".$controller."%", 'pack/sys');
            }
            $pack = $dataExp[0];
            $controller = $dataExp[1];

            // Construindo o nome do controller
            $fullControllerName="\\Controllers\\".$dataExp[0]."\\".$dataExp[1]."_Controller";
        }

        // Requerendo o arquivo do controller
        $controllerFilePath = INSTALL_DIR.DIRECTORY_SEPARATOR."packs".DIRECTORY_SEPARATOR.$pack.DIRECTORY_SEPARATOR."controllers".DIRECTORY_SEPARATOR.strtolower($controller)."_controller.php";
        if (file_exists($controllerFilePath)) {
            \KeyClass\FileTree::requireOnceFile($controllerFilePath);
        }
        else {
            \KeyClass\Error::i10nErrorRegister("File %".$controllerFilePath."% not found", 'pack/sys');
        }

        // Instanciando o controller com os parâmetros
        $C = new $fullControllerName($pack, $params);

        // Retornando controller instanciado
        return ($C);
    }

    /**
        Requisição de objeto model
     
        @author Marcello Costa

        @package KeyClass\Request
     
        @param  string  $model      Nome do model
        @param  string  $database   Nome do banco de dados (de acordo com as
                                    configurações do framework)
     
        @return object Retorna o modelo instanciado
    */
    public static function Model (string $model, string $database) : \KeyClass\Model {
        if (strpos($model, "::") === false) {
            // Tentando pegar o pack via explode
            $dataExp = explode("\\",$model);
            if (count($dataExp) !== 2) {
              \KeyClass\Error::i10nErrorRegister("The statement appears to be incorrect when requesting the model %".$model."%", 'pack/sys');
            }
            $pack = $dataExp[0];
            $model = $dataExp[1];

            // Construindo o nome do model
            $fullModelName="\\Models\\".$dataExp[0]."\\".$dataExp[1]."_Model";
        }
        else {
            $dataExp = explode('::',$model);
            if (count($dataExp) !== 2) {
              \KeyClass\Error::i10nErrorRegister("The statement appears to be incorrect when requesting the model %".$model."%", 'pack/sys');
            }
            $pack = $dataExp[0];
            $model = $dataExp[1];

            // Construindo o nome do model
            $fullModelName="\\Models\\".$dataExp[0]."\\".$dataExp[1]."_Model";
        }

        // Requerendo o arquivo do model
        $pathModelFile = INSTALL_DIR.DIRECTORY_SEPARATOR."packs".DIRECTORY_SEPARATOR.$pack.DIRECTORY_SEPARATOR."models".DIRECTORY_SEPARATOR.strtolower($model)."_model.php";
        if (file_exists($pathModelFile)) {
            \KeyClass\FileTree::requireOnceFile($pathModelFile);
        }
        else {
            \KeyClass\Error::i10nErrorRegister("File %".$pathModelFile."% not found", 'pack/sys');
        }

        // Instanciando o controller com os parâmetros
        $M = new $fullModelName($database);

        // Retornando model instanciado
        return ($M);
    }
}
