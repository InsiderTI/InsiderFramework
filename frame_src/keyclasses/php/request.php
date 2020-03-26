<?php
/**
  Arquivo KeyClass\Request
*/

// Namespace das KeyClass
namespace KeyClass;

/**
  KeyClass responsible for executing action requests on
    Models and Controllers

   @package KeyClass\Request

   @author Marcello Costa
*/
class Request{
    /**
        Instantiates a controller
     
        @author Marcello Costa

        @package KeyClass\Request
      
        @param  string $controller        Name of controller (with pack)
        @param  array  $params            Array of parameters that were received on request
     
        @return object Controller object
    */
    public static function Controller(string $controller, array $params=null) : \KeyClass\Controller {

        if (strpos($controller, "::") === false) {
            $dataExp = explode("\\",$controller);
            if (count($dataExp) !== 2) {
              \KeyClass\Error::i10nErrorRegister("The statement appears to be incorrect when requesting the controller %".$controller."%", 'pack/sys');
            }
            $pack = $dataExp[0];
            $controller = $dataExp[1];

            $fullControllerName="\\Controllers\\".$dataExp[0]."\\".$dataExp[1]."_Controller";
        }
        else {
            $dataExp = explode('::',$controller);
            if (count($dataExp) !== 2) {
              \KeyClass\Error::i10nErrorRegister("The statement appears to be incorrect when requesting the controller %".$controller."%", 'pack/sys');
            }
            $pack = $dataExp[0];
            $controller = $dataExp[1];

            $fullControllerName="\\Controllers\\".$dataExp[0]."\\".$dataExp[1]."_Controller";
        }

        $controllerFilePath = INSTALL_DIR.DIRECTORY_SEPARATOR."packs".DIRECTORY_SEPARATOR.$pack.DIRECTORY_SEPARATOR."controllers".DIRECTORY_SEPARATOR.strtolower($controller)."_controller.php";
        if (file_exists($controllerFilePath)) {
            \KeyClass\FileTree::requireOnceFile($controllerFilePath);
        }
        else {
            \KeyClass\Error::i10nErrorRegister("File %".$controllerFilePath."% not found", 'pack/sys');
        }

        $C = new $fullControllerName($pack, $params);

        return ($C);
    }

    /**
        Instantiates a model
     
        @author Marcello Costa

        @package KeyClass\Request
     
        @param  string  $model      Name of model
        @param  string  $database   Database Name (according to framework settings)
     
        @return object Returns the instantiated model
    */
    public static function Model (string $model, string $database) : \KeyClass\Model {
        if (strpos($model, "::") === false) {
            $dataExp = explode("\\",$model);
            if (count($dataExp) !== 2) {
              \KeyClass\Error::i10nErrorRegister("The statement appears to be incorrect when requesting the model %".$model."%", 'pack/sys');
            }
            $pack = $dataExp[0];
            $model = $dataExp[1];

            $fullModelName="\\Models\\".$dataExp[0]."\\".$dataExp[1]."_Model";
        }
        else {
            $dataExp = explode('::',$model);
            if (count($dataExp) !== 2) {
              \KeyClass\Error::i10nErrorRegister("The statement appears to be incorrect when requesting the model %".$model."%", 'pack/sys');
            }
            $pack = $dataExp[0];
            $model = $dataExp[1];

            $fullModelName="\\Models\\".$dataExp[0]."\\".$dataExp[1]."_Model";
        }

        $pathModelFile = INSTALL_DIR.DIRECTORY_SEPARATOR."packs".DIRECTORY_SEPARATOR.$pack.DIRECTORY_SEPARATOR."models".DIRECTORY_SEPARATOR.strtolower($model)."_model.php";
        if (file_exists($pathModelFile)) {
            \KeyClass\FileTree::requireOnceFile($pathModelFile);
        }
        else {
            \KeyClass\Error::i10nErrorRegister("File %".$pathModelFile."% not found", 'pack/sys');
        }

        $M = new $fullModelName($database);

        return ($M);
    }
}
