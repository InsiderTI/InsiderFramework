<?php

namespace Modules\InsiderFramework\Sagacious\Lib\Traits;

use Modules\InsiderFramework\Core\KernelSpace;
use Modules\InsiderFramework\Sagacious\Lib\SgsTemplate;
use Modules\InsiderFramework\Sagacious\Lib\SgsView;
use Modules\InsiderFramework\Core\Json;
use Modules\InsiderFramework\Core\FileTree;
use Modules\InsiderFramework\Core\Request;
use Modules\InsiderFramework\Sagacious\Lib\RenderEngine;

/**
 * Class with methods to be inject in Controller class of framework
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsController
 */
trait SgsController
{
    /**
     * Construct function of controller object
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Controller
     *
     * @return void
     */
    public function __construct()
    {
        $this->SgsView = new SgsView();

        $data = array(
            'controller' => $this
        );

        \Modules\InsiderFramework\Core\Middleware::call(
            'Modules\\InsiderFramework\\Core\\Controller::__construct',
            $data
        );
    }

    /**
     * Render a view to default output
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Controller
     *
     * @param string $viewFilename Pack/File name of view
     *
     * @return void
     */
    protected function renderView(string $viewFilename): void
    {
        // Getting who's calling the render method
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $app = null;
        if (isset($backtrace[0])) {
            if (isset($backtrace[0]['object'])) {
                $app = $backtrace[0]['object']->app;
            }
        }

        $this->SgsView->setViewFilename($viewFilename, $app);

        // Getting cache status and global route object
        $SagaciousCacheStatus = KernelSpace::getVariable(
            'SagaciousCacheStatus',
            'sagacious'
        );
        $routeObject = KernelSpace::getVariable(
            'routeObject',
            'RoutingSystem'
        );

        // Getting the route and action
        if ($routeObject !== null) {
            $route = $routeObject->getRoute();
            $actionNow = $routeObject->getActionNow();
        } else {
            $route = $app . "_INTERNAL_ERROR";
            $actionNow = $viewFilename . "_INTERNAL_ERROR";
        }

        // Name of cache file
        $cacheFileName = md5(
            $this->SgsView->getViewFilename() .
                $route . DIRECTORY_SEPARATOR .
                $actionNow
        );

        // Route to be registered/consulted on the cache file
        $route = str_replace("//", "/", $route . "/") . $actionNow;

        // Checking what is the status of cache on Sagacious
        if (is_bool($SagaciousCacheStatus)) {
            // If the cache is ativated
            if ($SagaciousCacheStatus === true) {
                // Getting the cache status of the file
                $cacheData = null;
                $cacheControl = $this->getCacheStatus(
                    $this->SgsView->getViewFilename(),
                    $cacheData
                );

                $renderfilepath = "Framework" . DIRECTORY_SEPARATOR .
                                  "Cache" . DIRECTORY_SEPARATOR .
                                  $cacheFileName . ".php";

                // If cache is invalid of if the file don't exists anymore
                // (was erased by another method)
                if (
                    $cacheControl['status'] !== 'valid' ||
                    !file_exists(INSTALL_DIR . DIRECTORY_SEPARATOR . $renderfilepath)
                ) {
                    // (re)creates the file
                    $viewconverted = $this->auxProcessViewCode($cacheFileName);
                }
            } else {
                // Always (re)creates the cache file on each request
                $viewconverted = $this->auxProcessViewCode($cacheFileName);
            }
        } else {
            // If time of cache is invalid
            if (!is_numeric($SagaciousCacheStatus)) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'Invalid time specified to cache of the view %' . $viewFilename . '%',
                    "app/sys"
                );
            } else {
                $SagaciousCacheStatus = floatval($SagaciousCacheStatus);
                KernelSpace::setVariable(
                    array('SagaciousCacheStatus' => $SagaciousCacheStatus),
                    'sagacious'
                );
            }

            // Searching the creation date of the file
            $renderfilepath = "Framework" . DIRECTORY_SEPARATOR .
                              "Cache" . DIRECTORY_SEPARATOR .
                              $cacheFileName . ".php";

            // Calculating
            $status = filemtime($renderfilepath);
            if ($status === false) {
                $viewconverted = $this->auxProcessViewCode($cacheFileName);
            } else {
                // Time (in seconds) since the creaction of the file
                $seconds = intval(time() - filemtime($renderfilepath));

                // If cache was expired
                if ($seconds > $SagaciousCacheStatus) {
                    $viewconverted = $this->auxProcessViewCode($cacheFileName);
                } else {
                    // Getting the cache status of the file
                    $cacheData = null;
                    $cacheControl = $this->getCacheStatus(
                        $this->SgsView->getViewFilename(),
                        $cacheData
                    );

                    $renderfilepath = "Framework" . DIRECTORY_SEPARATOR .
                                      "Cache" . DIRECTORY_SEPARATOR .
                                      $cacheFileName . ".php";

                    // If the cache was no longer valid or if the file do not exists anymore
                    // (was erased by another method)
                    if (
                        $cacheControl['status'] !== 'valid' ||
                        !file_exists(INSTALL_DIR . DIRECTORY_SEPARATOR . $renderfilepath)
                    ) {
                        // (re)creates the file
                        $viewconverted = $this->auxProcessViewCode($cacheFileName);
                    }
                }
            }
        }

        // If some code of the view was processed
        if (isset($viewconverted)) {
            // Logs the processed file(s) on cache
            $cacheControl = $this->addFileCacheControl(
                $viewconverted,
                $route,
                $cacheFileName . ".php"
            );
        }

        // Rendering the file on buffer and returning the result to variable
        $renderfilepath = "Framework" . DIRECTORY_SEPARATOR .
                          "Cache" . DIRECTORY_SEPARATOR .
                          $cacheFileName . ".php";

        // Setting the right include path
        set_include_path(get_include_path() . PATH_SEPARATOR . INSTALL_DIR);

        // If the php file exists in cache, renders it
        if (file_exists(INSTALL_DIR . DIRECTORY_SEPARATOR . $renderfilepath)) {
            // Require the php file
            if (ob_get_level() === 0) {
                ob_start();
            }
            FileTree::requireFile($renderfilepath);
            $view = ob_get_contents();

            Request::clearAndRestartBuffer();
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'Cache file not found %' . $renderfilepath . '% on requesting the view %' .
                    $this->SgsView->getViewFilename() . '%',
                "app/sys"
            );
        }

        // Displaying the view
        $responseFormat = KernelSpace::getVariable(
            'responseFormat',
            'insiderFrameworkSystem'
        );

        switch ($responseFormat) {
            case 'JSON':
            case 'XML':
                $this->responseAPI($view);
                break;

            default:
                echo $view;
                break;
        }
    }

    /**
     * Render a view and returns the result as a string
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Controller
     *
     * @param string $viewFilename Pack/File name of view
     *
     * @return string Renderized view
     */
    public function renderViewToString(string $viewFilename): string
    {
        if (ob_get_level() === 0) {
            ob_start();
        }
        $this->renderView($viewFilename);

        $renderedView = ob_get_contents();

        Request::clearAndRestartBuffer();
        ob_end_clean();

        return $renderedView;
    }

    /**
     * Auxiliary function to create the cache file to be render
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Controller
     *
     * @param string $fileName Name of the file to be created (without extension)
     *
     * @return array Processed data on views and related templates
     */
    protected function auxProcessViewCode(string &$fileName): array
    {
        // Deleting old cache file
        if (
            file_exists(
                INSTALL_DIR .
                DIRECTORY_SEPARATOR .
                "Framework" . DIRECTORY_SEPARATOR .
                "Cache" . DIRECTORY_SEPARATOR .
                $fileName . ".php"
            )
        ) {
            FileTree::deleteFile(
                INSTALL_DIR . DIRECTORY_SEPARATOR .
                "Framework" . DIRECTORY_SEPARATOR .
                "Cache" . DIRECTORY_SEPARATOR .
                $fileName . ".php"
            );
        }

        // Convert template code + view into one php file
        $temp = new SgsTemplate();
        $codev = $temp->convertSGV2PHP($this->SgsView);

        // Writing to file
        FileTree::fileWriteContent(
            INSTALL_DIR . DIRECTORY_SEPARATOR .
            "Framework" . DIRECTORY_SEPARATOR .
            "Cache" . DIRECTORY_SEPARATOR .
            $fileName . ".php",
            $codev['renderCode']
        );

        // Returning data from the file that was created
        return $codev;
    }

    /**
     * Adds an file to the cache control
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Controller
     *
     * @param array  $viewConverted Data of the view(s) and template(s)
     * @param string $route         Route that originally requested the file
     * @param string $cacheFileName Name of file to be stored
     *
     * @return array Cache data of file
     */
    private function addFileCacheControl(
        array $viewConverted,
        string $route,
        string $cacheFileName
    ): array {
        $cacheDirectory = INSTALL_DIR . DIRECTORY_SEPARATOR .
                        "Framework" . DIRECTORY_SEPARATOR .
                        "Cache";

        if (!is_dir($cacheDirectory)) {
            FileTree::createDirectory($$cacheDirectory, 777);
        }

        $viewsCacheControlFile = $cacheDirectory . DIRECTORY_SEPARATOR .
                                "ViewsCacheControl.json";

        $cacheData = null;
        if (!file_exists($viewsCacheControlFile)) {
            Json::setJSONDataFile([], $viewsCacheControlFile);
        }

        $cacheData = Json::getJSONDataFile($viewsCacheControlFile);

        // If an error occours while trying to read the JSON
        if ($cacheData === null) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'An error occured on trying to read the JSON file that ' .
                    'controls cache',
                "app/sys"
            );
        }

        // If cache file did not exists, returns the value returned
        // by the getCacheStatus function (invalid)
        if (
            !file_exists(
                $cacheDirectory . DIRECTORY_SEPARATOR .
                $cacheFileName
            )
        ) {
            return $this->getCacheStatus($cacheFileName, $cacheData);
        }

        // The view that will be the index of array is the first one who is detected
        // (because is the one who originate the request)
        $originalFileName = $viewConverted['viewsPath'][0];

        // If the cache control file exists
        if ($cacheData !== false) {
            // Checking if the cache file exists and is valid
            $cacheState = $this->getCacheStatus($originalFileName, $cacheData);

            // If the registry of cache file did not exists or is invalid
            if ($cacheState['status'] === 'notexist' || $cacheState['status'] === 'invalid') {
                $data = [];
                $data[$originalFileName] = [
                    'cacheFileName' => $cacheFileName,
                    'route' => $route
                ];
            }

            // Setting the status of cache as valid to be recreated
            $statusCache = 'valid';
        } else {
            $data = [];

            // Storing cache data
            $data[$originalFileName] = [
                'cacheFileName' => $cacheFileName,
                'route' => $route
            ];

            // The cache status is valid, because the view will be mapped and
            // cached for the first time
            $statusCache = 'valid';
        }

        // If there is data to be stored
        if (isset($data)) {
            // Searching the modification date of the views and templates
            $viewsData = [];
            $templatesData = [];

            // For each view
            foreach ($viewConverted['viewsPath'] as $vP) {
                // Searching for the modification date of the file
                $dateModify = filemtime(INSTALL_DIR . DIRECTORY_SEPARATOR . $vP);

                // Inserting in the data array
                $viewsData[$vP] = $dateModify;
            }

            // For each template
            foreach ($viewConverted['templatesPath'] as $tP) {
                // Searching the modification date of the file
                $dateModify = filemtime($tP);

                // Inserting in the data array
                $templatesData[$tP] = $dateModify;
            }

            // Inserting in the end of data array
            $data[$originalFileName]['views'] = $viewsData;
            $data[$originalFileName]['templates'] = $templatesData;

            // Replacing/inserting the data
            $cacheData[$originalFileName] = $data[$originalFileName];

            // Recording the view cache configuration
            $write = Json::setJSONDataFile(
                $data,
                $viewsCacheControlFile,
                true
            );
            if ($write !== true) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'An error occured on trying ' .
                    'to write the cache file of views',
                    "app/sys"
                );
            }
        }

        // Returning the status of cache
        return array(
            'status' => $statusCache
        );
    }

    /**
    * Function to verify cache of a file
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Controller
    *
    * @param string $originalFileName View file that originated the cache
    * @param array  $cacheData        JSON cache file previously read
    *
    * @return array Returns the data about the cache file
    */
    private function getCacheStatus(string $originalFileName, array &$cacheData = null): array
    {
        // If cache status is false = The cache file was not created yet
        // If cache status is null = The cache file was not read yet
        if ($cacheData === null) {
            $cacheData = Json::getJSONDataFile(
                INSTALL_DIR . DIRECTORY_SEPARATOR .
                "Framework" . DIRECTORY_SEPARATOR .
                "Cache" . DIRECTORY_SEPARATOR .
                "ViewsCacheControl.json"
            );

            // If still equals to null, an error occurs on JSON read
            if ($cacheData === null) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'An error occured on trying to read the JSON file that controls cache',
                    "app/sys"
                );
            }
        }

        // If the file is in cache
        if ($cacheData !== false && array_key_exists($originalFileName, $cacheData)) {
            // Cache status (initialized as valid)
            $status = 'valid';

            // For each file in view, checks if the file has modifications
            foreach ($cacheData[$originalFileName]['views'] as $viewFilePath => $dateModifyCache) {
                // Searching for the modification date of the file
                $dateModifyNow = filemtime(INSTALL_DIR . DIRECTORY_SEPARATOR . $viewFilePath);

                // If something was modified after the cache is builded
                if ($dateModifyNow !== $dateModifyCache) {
                    $status = 'invalid';
                }
            }

            // For each template file, checks if the file has modifications
            foreach ($cacheData[$originalFileName]['templates'] as $templateFilePath => $dateModifyCache) {
                // Searching for the modification date of the file
                $dateModifyNow = filemtime($templateFilePath);

                // If something was modified after the cache is builded
                if ($dateModifyNow !== $dateModifyCache) {
                    $status = 'invalid';
                }
            }

            // Returning the cache status
            return array(
                'status' => $status
            );
        } else {
            return array(
                'status' => 'notexist'
            );
        }
    }
}
