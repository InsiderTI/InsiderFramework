<?php

namespace Modules\InsiderFramework\Sagacious\Lib;

use Modules\InsiderFramework\Core\KernelSpace;
use Modules\InsiderFramework\Core\Manipulation\Registry;
use Modules\InsiderFramework\Core\FileTree;
use Modules\InsiderFramework\Sagacious\Lib\SgsComponentState;
use Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsComponentsBag;

/**
 * Classe responsável pelo objeto SgsView.
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
 */
class SgsView
{
    /** @var string Nome da view */
    private $viewFilename;

    /** @var string App do objeto SgsView */
    private $app;

    /**
     * Função para recuperar o nome do arquivo da view
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
     *
     * @return string Nome do arquivo
     */
    public function getViewFilename(): string
    {
        return $this->viewFilename;
    }

    /**
     * Função para setar o nome do arquivo da view
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
     *
     * @param string $viewFilename Nome do arquivo
     * @param string $app         Nome do arquivo
     *
     * @return void
     */
    public function setViewFilename(string $viewFilename, string $app = null): void
    {
        $pattern = "/" . "((?P<app>.*)::)?(?P<viewPath>.*)" . "/";

        // Se não foi encontrada uma tag literal
        preg_match_all($pattern, $viewFilename, $viewFilenameMatches, PREG_SET_ORDER);

        if (is_array($viewFilenameMatches) && count($viewFilenameMatches) > 0) {
            $viewData = $viewFilenameMatches[0];
            $viewFilename = $viewData['viewPath'];

            // Se foi especificado app via viewFilename
            if (trim($viewData['app']) !== "") {
                $app = $viewData['app'];
            }
        }

        // Se também não foi especificado o app via parâmetros da função
        if ($app == null) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'Unable to identify the origin of request to the view %' .
                $SgsView->getViewFilename() .
                '%',
                "app/sys"
            );
        }

        $this->setApp($app);

        $this->viewFilename = "apps" . DIRECTORY_SEPARATOR .
        $app . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR .
        $viewFilename;
    }

    /**
     * Recupera o app do objeto
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
     *
     * @return string Nome do app
     */
    public function getApp(): string
    {
        return $this->app;
    }

    /**
     * Seta o app do objeto
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
     *
     * @param string $app Nome do app
     *
     * @return void
     */
    public function setApp($app): void
    {
        $this->app = $app;
    }

    /**
    * This functions it's used to every view. It's an init routine.
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
    *
    * @param string $componentId Id of components of view
    *
    * @return void
    */
    public static function initializeViewCode(string $componentsId): void
    {
        if (DEBUG_BAR == true) {
            $timer = KernelSpace::getVariable('timer', 'insiderFrameworkSystem');
            $timer->debugBar('render');
        }

        $componentsTemporaryIdArray = json_decode($componentsId);

        global $kernelspace;

        foreach ($componentsTemporaryIdArray as $componentTemporaryId) {
            $componentDataRequestedFromView = KernelSpace::getVariable(
                'viewComponentsInfo' . $componentTemporaryId,
                'sagacious'
            );

            if (empty($componentDataRequestedFromView)) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'Unable to recover component data from componentsbag ' . $componentTemporaryId,
                    "app/sys"
                );
            }

            SgsComponentsBag::initializeComponentComponentsBag($componentDataRequestedFromView);
        }
    }

    /**
    * Runs the execute function of SgsComponents
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
    *
    * @param string $componentId  Component Id
    * @param string $typeFunction Call type function
    *
    * @return mixed Any return of the target function
    */
    public static function executeComponentFunction($componentId, $typeFunction)
    {
        $componentsBag = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'componentsBag',
            'sagacious'
        );

        $component = $componentsBag->get($componentId);

        return call_user_func(array($component, $typeFunction));
    }
}
