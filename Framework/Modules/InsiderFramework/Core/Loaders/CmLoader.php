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
                         "Apps" . DIRECTORY_SEPARATOR .
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
