<?php

// Namespace das KeyClass
namespace Modules\InsiderFramework\Core\Loaders;

use Modules\InsiderFramework\Core\Controller;

/**
 *  KeyClass for loading controllers and models of framework
 *
 *  @author Marcello Costa
 *
 *  @package Modules\InsiderFramework\Core\Loaders\CmLoader
 */
class CmLoader
{
    /**
     * Instantiates a controller
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Request
     *
     * @param string $controller Name of controller (with app)
     * @param array  $params     Array of parameters that were received on request
     *
     * @return object Controller object
     */
    public static function controller(string $controller, array $params = null): Controller
    {
        if (strpos($controller, "::") === false) {
            $dataExp = explode("\\", $controller);
            if (count($dataExp) !== 2) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "The statement appears to be incorrect when requesting the controller %" . $controller . "%",
                    "app/sys"
                );
            }
            $app = $dataExp[0];
            $controller = $dataExp[1];

            $fullControllerName = "\\Controllers\\" . $dataExp[0] . "\\" . $dataExp[1] . "Controller";
        } else {
            $dataExp = explode('::', $controller);
            if (count($dataExp) !== 2) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "The statement appears to be incorrect when requesting the controller %" . $controller . "%",
                    "app/sys"
                );
            }
            $app = $dataExp[0];
            $controller = $dataExp[1];

            $fullControllerName = "\\Controllers\\" . $dataExp[0] . "\\" . $dataExp[1] . "Controller";
        }

        $controllerFilePath = INSTALL_DIR . DIRECTORY_SEPARATOR .
                              "apps" . DIRECTORY_SEPARATOR .
                              $app . DIRECTORY_SEPARATOR .
                              "controllers" . DIRECTORY_SEPARATOR .
                              strtolower($controller) . "_controller.php";

        if (file_exists($controllerFilePath)) {
            \Modules\InsiderFramework\Core\FileTree::requireOnceFile($controllerFilePath);
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "File %" . $controllerFilePath . "% not found",
                "app/sys"
            );
        }

        $C = new $fullControllerName($app, $params);

        return ($C);
    }

    /**
     * Instantiates a model
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Request
     *
     * @param string $model    Name of model
     * @param string $database Database Name (according to framework settings)
     *
     * @return object Returns the instantiated model
     */
    public static function model(string $model, string $database): \Modules\InsiderFramework\Core\Model
    {
        if (strpos($model, "::") === false) {
            $dataExp = explode("\\", $model);
            if (count($dataExp) !== 2) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "The statement appears to be incorrect when requesting the model %" . $model . "%",
                    "app/sys"
                );
            }
            $app = $dataExp[0];
            $model = $dataExp[1];

            $fullModelName = "\\Models\\" . $dataExp[0] . "\\" . $dataExp[1] . "_Model";
        } else {
            $dataExp = explode('::', $model);
            if (count($dataExp) !== 2) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "The statement appears to be incorrect when requesting the model %" . $model . "%",
                    "app/sys"
                );
            }
            $app = $dataExp[0];
            $model = $dataExp[1];

            $fullModelName = "\\Models\\" . $dataExp[0] . "\\" . $dataExp[1] . "_Model";
        }

        $pathModelFile = INSTALL_DIR . DIRECTORY_SEPARATOR .
                         "apps" . DIRECTORY_SEPARATOR .
                         $app . DIRECTORY_SEPARATOR .
                         "models" . DIRECTORY_SEPARATOR .
                         strtolower($model) . "_model.php";

        if (file_exists($pathModelFile)) {
            \Modules\InsiderFramework\Core\FileTree::requireOnceFile($pathModelFile);
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "File %" . $pathModelFile . "% not found",
                "app/sys"
            );
        }

        $M = new $fullModelName($database);

        return ($M);
    }
}
