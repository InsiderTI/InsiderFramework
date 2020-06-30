<?php

namespace Modules\InsiderFramework\Sagacious\Lib;

use Modules\InsiderFramework\Core\KernelSpace;
use Modules\InsiderFramework\Core\Manipulation\Registry;
use Modules\InsiderFramework\Core\FileTree;
use Modules\InsiderFramework\Sagacious\Lib\SgsComponentState;
use Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsComponentsBag;

/**
 * Class responsible for the SgsView object.
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
 */
class SgsView
{
    /** @var string Name of view */
    private $viewFilename;

    /** @var string SgsView object app */
    private $app;

    /**
     * Function to retrieve the file name from the view
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
     *
     * @return string File name
     */
    public function getViewFilename(): string
    {
        return $this->viewFilename;
    }

    /**
     * Function to set the file name of the view
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
     *
     * @param string $viewFilename View file name
     * @param string $app          Name of app
     *
     * @return void
     */
    public function setViewFilename(string $viewFilename, string $app = null): void
    {
        $pattern = "/" . "((?P<app>.*)::)?(?P<viewPath>.*)" . "/";

        preg_match_all($pattern, $viewFilename, $viewFilenameMatches, PREG_SET_ORDER);

        if (is_array($viewFilenameMatches) && count($viewFilenameMatches) > 0) {
            $viewData = $viewFilenameMatches[0];
            $viewFilename = $viewData['viewPath'];

            if (trim($viewData['app']) !== "") {
                $app = $viewData['app'];
            }
        }

        if ($app == null) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'Unable to identify the origin of request to the view %' .
                $SgsView->getViewFilename() .
                '%',
                "app/sys"
            );
        }

        $this->setApp($app);

        $this->viewFilename = "Apps" . DIRECTORY_SEPARATOR .
        $app . DIRECTORY_SEPARATOR . "Views" . DIRECTORY_SEPARATOR .
        $viewFilename;
    }

    /**
     * Retrieves the object app
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
     *
     * @return string Name of app
     */
    public function getApp(): string
    {
        return $this->app;
    }

    /**
     * Set the object's app
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsView
     *
     * @param string $app Name of app
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
