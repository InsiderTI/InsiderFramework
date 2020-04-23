<?php

namespace Modules\InsiderFramework\Core;

use Modules\InsiderFramework\Core\KernelSpace;
use Modules\InsiderFramework\Core\FileTree;
use Modules\InsiderFramework\Core\Request;
use Modules\InsiderFramework\Core\Json;
use Modules\InsiderFramework\Core\Xml;
use Modules\InsiderFramework\Core\Aggregation;
use Modules\InsiderFramework\Sagacious\Lib\SgsView;
use Modules\InsiderFramework\Sagacious\Lib\SgsTemplate;

/**
 * Object class for Controllers
 * 
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @link    https://www.insiderframework.com/documentation/keyclass#controller
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * 
 * @package Modules\InsiderFramework\Core\Controller
 */
class Controller
{
    /**
     * Storages the name of the app the controller belongs to
     *
     * @var string $app
     */
    public $app = "";

    /**
     * Parameters sended to the controller action
     *
     * @var string $params
     */
    protected $params = "";

    /**
     * SgsView object used to render views
     *
     * @var object SgsView
     **/
    protected $SgsView;

    /**
     * Construct function of controller object
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Controller
     *
     * @param string $app   Controller app name
     * @param mixed  $params Parameters which will be used by the action
     *                       on controller
     * 
     * @return void
     */
    public function __construct(string $app, $params = null)
    {
        $this->app = $app;
        $this->params = $params;
        $this->SgsView = new SgsView();

        // Global variables of components
        $componentsBag = KernelSpace::getVariable(
            'componentsBag',
            'sagacious'
        );
        $viewsBag = KernelSpace::getVariable(
            'viewsBag',
            'sagacious'
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

                $renderfilepath = "framework" . DIRECTORY_SEPARATOR .
                                  "cache" . DIRECTORY_SEPARATOR .
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
            $renderfilepath = "framework" .
                DIRECTORY_SEPARATOR .
                "cache" .
                DIRECTORY_SEPARATOR .
                $cacheFileName .
                ".php";

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

                    $renderfilepath = "framework" . DIRECTORY_SEPARATOR .
                                      "cache" . DIRECTORY_SEPARATOR .
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
        $renderfilepath = "framework" . DIRECTORY_SEPARATOR .
                          "cache" . DIRECTORY_SEPARATOR .
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
                          "framework" . DIRECTORY_SEPARATOR .
                          "cache";

        if (!is_dir($cacheDirectory)) {
            FileTree::createDirectory($$cacheDirectory, 777);
        }

        $viewsCacheControlFile = $cacheDirectory . DIRECTORY_SEPARATOR .
                                 "views_cache_control.json";

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
                "framework" . DIRECTORY_SEPARATOR .
                "cache" . DIRECTORY_SEPARATOR .
                "views_cache_control.json"
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
                "framework" . DIRECTORY_SEPARATOR .
                "cache" . DIRECTORY_SEPARATOR .
                $fileName . ".php"
            )
        ) {
            FileTree::deleteFile(
                INSTALL_DIR . DIRECTORY_SEPARATOR .
                "framework" . DIRECTORY_SEPARATOR .
                "cache" . DIRECTORY_SEPARATOR .
                $fileName . ".php"
            );
        }

        // Convert template code + view into one php file
        $temp = new SgsTemplate();
        $codev = $temp->convertSGV2PHP($this->SgsView);

        // Writing to file
        FileTree::fileWriteContent(
            INSTALL_DIR . DIRECTORY_SEPARATOR .
            "framework" . DIRECTORY_SEPARATOR .
            "cache" . DIRECTORY_SEPARATOR .
            $fileName . ".php",
            $codev['renderCode']
        );

        // Returning data from the file that was created
        return $codev;
    }

    /**
     * Convert an string received in the request
     *
     * @author Marcello Costa <marcello88costa@yahoo.com.br>
     *
     * @package Modules\InsiderFramework\Core\Controller
     * 
     * @param string $data        Data received in request
     * @param bool   $origjson    If the data received are JSON
     * @param bool   $returnarray If the return must be an array
     * 
     * @return mixed Unknown type of return
     */
    protected function convertDataOfPost(
        string $data,
        bool $origjson = true,
        bool $returnarray = true
    ) {
        // If it's an JSON
        if ($origjson) {
            $newdata = str_replace("\/", "/", $data);

            // If the data converted are an valid JSON
            if (\Modules\InsiderFramework\Core\Json::isJSON($newdata)) {
                // If the return it's an array
                if ($returnarray === true) {
                    return (json_decode($newdata, true));
                } else {
                    return (json_decode($newdata));
                }
            } else {
                return false;
            }
        } else {
            $newdata = str_replace("\/", "/", $data);
            return $newdata;
        }
    }

    /**
     * Return an JSON as an response of controller (method)
     *
     * @param mixed $data         Data to be returned
     * @param int   $responseCode Response code
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Controller
     *
     * @return void
     */
    protected function responseJson($data, int $responseCode = null): void
    {
        if ($responseCode !== null) {
            http_response_code($responseCode);
        }
        header('Content-Type: application/json');
        echo Json::jsonEncodePrivateObject($data);
    }

    /**
     * Return an XML as an response of controller (method)
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Controller
     * 
     * @param mixed $data           Data to be returned
     * @param bool  $fixNumericKeys If true, set an preffix
     *                              to the numeric keys of XML
     * @param int   $responseCode   Response code
     *
     * @return void
     */
    protected function responseXML(
        $data,
        bool $fixNumericKeys = true,
        int $responseCode = null
    ): void {
        header('Content-type: text/xml');

        if ($fixNumericKeys === null) {
            $fixNumericKeys = false;
        }

        // Array
        if (is_array($data)) {
            $xmlObj = "";
            $xml = Xml::arrayToXML($data, $xmlObj, $fixNumericKeys);
            echo $xml->asXML();
        } elseif (is_string($data) || (is_int($data)) || is_float($data) || is_bool($data)) { // String
            $xml = new \SimpleXMLElement('<root/>');
            $xml->addChild("root", $data);
            echo $xml->asXML();
        } elseif (is_object($data)) { // Object
            $data = Aggregation::objectToArray($data);
            $this->responseXML($data);
        } elseif (is_resource($data)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'It is not possible to convert a resource into an XML',
                "app/sys"
            );
        } elseif ($data === null) {
            $xml = new \SimpleXMLElement('<root/>');
            $xml->addChild("root", "");
            echo $xml->asXML();
        } else { // Invalid/unknow type
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'It is not possible to convert a resource into an XML',
                "app/sys"
            );
        }
    }

    /**
     * Return an API response for the action of controller (method)
     * 
     * @author Marcello Costa <marcello88costa@yahoo.com.br>
     *
     * @package Modules\InsiderFramework\Core\Controller
     * 
     * @param mixed $data      Data to be returned
     * @param array $arrayArgs Additional arguments to the function
     *
     * @return void
     */
    protected function responseAPI($data, array $arrayArgs = []): void
    {
        $debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        // If some error occours
        if (
            isset($debugBacktrace[0]) &&
            isset($debugBacktrace[0]["object"]) &&
            isset($debugBacktrace[0]["args"]) &&
            get_class($debugBacktrace[0]["object"]) === "Controllers\sys\ErrorController"
        ) {
            if (DEBUG) {
                $data = array(
                    "error" => $debugBacktrace
                );
            } else {
                $data = $debugBacktrace[0]["args"][0];
            }
        }

        if (\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($arrayArgs, 'responseFormat')) {
            $responseFormat = $arrayArgs['responseFormat'];
        } else {
            $responseFormat = KernelSpace::getVariable(
                'responseFormat',
                'insiderFrameworkSystem'
            );
            if ($responseFormat === "") {
                $responseFormat = DEFAULT_RESPONSE_FORMAT;
                KernelSpace::setVariable(
                    array(
                        'responseFormat' => $responseFormat
                    ),
                    'insiderFrameworkSystem'
                );
            }
        }

        switch ($responseFormat) {
            case "JSON":
                $responseCode = null;
                if (is_array($arrayArgs) && count($arrayArgs) > 0) {
                    $arrayArgs = array_change_key_case($arrayArgs, CASE_LOWER);
                    if (isset($arrayArgs['responsecode'])) {
                        $responseCode = intval($arrayArgs['responsecode']);
                    }
                }
                $this->responseJson($data, $responseCode);
                break;

            case "XML":
                $fixNumericKeys = true;
                $responseCode = null;
                if (is_array($arrayArgs) && count($arrayArgs) > 0) {
                    $arrayArgs = array_change_key_case($arrayArgs, CASE_LOWER);
                    if (isset($arrayArgs['responsecode'])) {
                        $responseCode = intval($arrayArgs['responseCode']);
                    }
                    if (isset($arrayArgs['fixnumerickeys'])) {
                        $fixNumericKeys = $arrayArgs['fixnumerickeys'];
                    }
                }
                $this->responseXML($data, $fixNumericKeys, $responseCode);
                break;

            default:
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'Invalid response type for API: %' . $responseFormat . '%',
                    "app/sys"
                );
                break;
        }
    }

    /**
     * Serve files with http
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Controller
     *
     * @param string $originalFilePath File path of the file to be served
     * @param string $serveFileName    File name of the file to be served
     * @param string $contentType      Type of file
     * @param float  $downloadRate     Download rate for download. When
     *                                 specified 0, no limit will be applied
     *
     * @return void
     */
    public function serveFile(
        string $originalFilePath,
        string $serveFileName,
        string $contentType = "application/octet-stream",
        float $downloadRate = 0
    ): void {
        if (file_exists($originalFilePath) && is_file($originalFilePath)) {
            // Setting headers
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: filename=' . $originalFilePath);
            header('Cache-control: private');
            header('Content-Length: ' . filesize($originalFilePath));

            // Opening the file
            $file = fopen($originalFilePath, "r");
            print fread($file, filesize($originalFilePath));
            flush();
            fclose($file);
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister(
                'Error: The file ' . $originalFilePath .
                    ' does not exist!'
            );
        }
    }
}
