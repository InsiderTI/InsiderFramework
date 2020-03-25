<?php
/**
  KeyClass\Controller
*/

// Namespace of KeyClass
namespace KeyClass;

// Namespace of Sagacious
use Sagacious;

/**
  Object class for Controllers
  
  @author Marcello Costa
  
  @package KeyClass\Controller
*/
class Controller{
    /** @var string controller pack */
    public $pack="";
    /** @var string Parameters sended to the controller action */
    protected $params="";
    /** @var object SgsView */
    protected $views="";
    /** @var object SgsComponentsBag */
    public $componentsBag="";
    /** @var object SgsView */
    protected $SgsView="";

    /**
        Construct function of controller object
      
        @author Marcello Costa
     
        @package KeyClass\Controller
      
        @param  string  $pack          Controller pack name
        @param  mixed   $params        Parameters which will be used by the action on controller
     
        @return Void
    */
    public function __construct(string $pack, $params=null) {
        global $kernelspace;
        $this->pack=$pack;
        $this->params=$params;

        // Global variables of components
        $kernelspace->setVariable(array('componentsBag' => array()), 'insiderFrameworkSystem');
        $componentsBag = $kernelspace->getVariable('componentsBag', 'insiderFrameworkSystem');
        $loadedHelpers = $kernelspace->getVariable('loadedHelpers', 'insiderFrameworkSystem');
        if ($loadedHelpers === null) {
            $loadedHelpers=[];
        }

        // Attach componentsBag to controller
        $this->componentsBag=&$componentsBag;

        // If directory of helpers exists in this pack
        if (is_dir(INSTALL_DIR.DIRECTORY_SEPARATOR."packs".DIRECTORY_SEPARATOR.$pack.DIRECTORY_SEPARATOR."helpers")) {
            // Searching php files on directory of helpers
            $helperFiles = glob(INSTALL_DIR.DIRECTORY_SEPARATOR."packs".DIRECTORY_SEPARATOR.$pack.DIRECTORY_SEPARATOR."helpers".DIRECTORY_SEPARATOR."*.{php}", GLOB_BRACE);

            // For each directory/file inside the helpers directory
            foreach($helperFiles as $hF) {
                $resumedPath = str_replace(INSTALL_DIR, "", $hF);
                if (!in_array($resumedPath,$loadedHelpers)) {
                    // Requesting the helper file
                    \KeyClass\FileTree::requireOnceFile($hF);
                    $loadedHelpers[]=$resumedPath;
                }
            }
            unset($resumedPath);
        }

        // Updating loaded helpers
        $kernelspace->setVariable(array('loadedHelpers' => $loadedHelpers), 'insiderFrameworkSystem');

        // Calling the construct method of helper (if exists)
        if (method_exists($this,'customConstruct')) {
            call_user_func_array(array($this,'customConstruct'),array(serialize($this)));
        }
    }

    /**
        Render a view to default output
      
        @author Marcello Costa
        
        @package KeyClass\Controller
      
        @param  string  $viewFilename    Pack/File name of view
     
        @return Void
    */
    protected function renderView(string $viewFilename) : void {
        global $kernelspace;
        
        // Getting who's calling the render method
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $pack=null;
        if (isset($backtrace[0])) {
           if (isset($backtrace[0]['object'])) {
               $pack=$backtrace[0]['object']->pack;
           }
        }

        // Initializing the SgsView object
        $this->SgsView = new \Sagacious\SgsView();
        $this->SgsView->setViewFilename($viewFilename, $pack);

        // Getting cache status and global route object
        $SagaciousCacheStatus = $kernelspace->getVariable('SagaciousCacheStatus', 'sagacious');
        $routeObject = $kernelspace->getVariable('routeObject', 'insiderRoutingSystem');

        // Getting the route and action
        if ($routeObject !== null){
            $route = $routeObject->getRoute();
            $actionNow = $routeObject->getActionNow();
        }
        // If an internal error occurs, the routeObject will be null. So, 
        // another approach is used to generate the variables
        else{
            $route = $pack."_INTERNAL_ERROR";
            $actionNow = $viewFilename."_INTERNAL_ERROR";
        }
 
        // Name of cache file
        $cacheFileName=md5($this->SgsView->getViewFilename().$route.DIRECTORY_SEPARATOR.$actionNow);

        // Route to be registered/consulted on the cache file
        $route = str_replace("//", "/", $route."/").$actionNow;

        // Checking what is the status of cache on Sagacious
        if (is_bool($SagaciousCacheStatus)) {
          // If the cache is ativated
          if ($SagaciousCacheStatus === true) {
            // Getting the cache status of the file
            $cacheData = null;
            $cacheControl = $this->checkCache($this->SgsView->getViewFilename(), $cacheData);

            $renderfilepath="frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$cacheFileName.".php";

            // If cache is invalid of if the file don't exists anymore (was erased by another method)
            if ($cacheControl['status'] !== 'valid' || !file_exists(INSTALL_DIR.DIRECTORY_SEPARATOR.$renderfilepath)) {
                // (re)creates the file
                $viewconverted=$this->auxProcessViewCode($cacheFileName);
            }
          }
          // If the cache is not ativated
          else {
            // Always (re)creates the cache file on each request
            $viewconverted=$this->auxProcessViewCode($cacheFileName);
          }
        }
        // If the timeout of cache was defined by the user
        else {
          // If time of cache is invalid  
          if (!is_numeric($SagaciousCacheStatus)) {
            \KeyClass\Error::i10nErrorRegister('Invalid time specified to cache of the view %'.$viewFilename.'%', 'pack/sys');
          }
          else {
            $SagaciousCacheStatus = floatval($SagaciousCacheStatus);
            $kernelspace->setVariable(array('SagaciousCacheStatus' => $SagaciousCacheStatus), 'sagacious');
          }

          // Searching the creation date of the file
          $renderfilepath="frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$cacheFileName.".php";

          // Calculating
          $status = filemtime($renderfilepath);
          if ($status === false) {
            $viewconverted=$this->auxProcessViewCode($cacheFileName);
          }
          else {
            // Time (in seconds) since the creaction of the file
            $seconds = intval(time()-filemtime($renderfilepath));

            // If cache was expired
            if ($seconds > $SagaciousCacheStatus) {
              $viewconverted=$this->auxProcessViewCode($cacheFileName);
            }
            // If cache still not expired
            else {
              // Getting the cache status of the file
              $cacheData = null;
              $cacheControl = $this->checkCache($this->SgsView->getViewFilename(), $cacheData);

              $renderfilepath="frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$cacheFileName.".php";

              // If the cache was no longer valid or if the file do not exists anymore (was erased by another method)
              if ($cacheControl['status'] !== 'valid' || !file_exists(INSTALL_DIR.DIRECTORY_SEPARATOR.$renderfilepath)) {
                  // (re)creates the file
                  $viewconverted=$this->auxProcessViewCode($cacheFileName);
              }
            }
          }
        }

        // If some code of the view was processed
        if (isset($viewconverted)) {
            // Logs the processed file(s) on cache
            $cacheControl = $this->addFileCacheControl($viewconverted, $route, $cacheFileName.".php");
        }

        // Rendering the file on buffer and returning the result to variable
        $renderfilepath="frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$cacheFileName.".php";

        // Setting the right include path
        set_include_path(get_include_path() . PATH_SEPARATOR . INSTALL_DIR);

        // If the php file exists in cache, renders it
        if (file_exists(INSTALL_DIR.DIRECTORY_SEPARATOR.$renderfilepath)) {
            // Require the php file
            if (ob_get_level() === 0){
                ob_start();
            }
            \KeyClass\FileTree::requireFile($renderfilepath);
            $view = ob_get_contents();
            
            clearAndRestartBuffer();
            ob_end_clean();
        }

        // If the php file do not exists in cache, throws and error
        else {
            \KeyClass\Error::i10nErrorRegister('Cache file not found %'.$renderfilepath.'% on requesting the view %'.$this->SgsView->getViewFilename().'%', 'pack/sys');
        }

        // Displaying the view
        $responseFormat = $kernelspace->getVariable('responseFormat', 'insiderFrameworkSystem');

        switch($responseFormat) {
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
        Render a view and returns the result as a string
      
        @author Marcello Costa
        
        @package KeyClass\Controller
      
        @param  string  $viewFilename    Pack/File name of view
     
        @return Void
    */
    protected function renderViewToString(string $viewFilename) : string {
        if (ob_get_level() === 0){
            ob_start();
        }
        $this->renderView($viewFilename);
        $renderedView = ob_get_contents();

        clearAndRestartBuffer();
        ob_end_clean();
        
        return $renderedView;
    }

    /**
        Adds an file to the cache control
      
        @author Marcello Costa
      
        @package KeyClass\Controller
     
        @param  array   $viewConverted       Data of the view(s) and template(s)
        @param  string  $route               Route that originally requested the file
        @param  string  $cacheFileName       Name of file to be stored
     
        @return array   Cache data of file
   */
    private function addFileCacheControl(array $viewConverted, string $route, string $cacheFileName) : array {
        $viewsCacheControlFile = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR."views_cache_control.json";
        
        $cacheData = null;
        if (!file_exists($viewsCacheControlFile)){
            \KeyClass\JSON::setJSONDataFile([], $viewsCacheControlFile);
        }

        $cacheData = \KeyClass\JSON::getJSONDataFile($viewsCacheControlFile);

        // If an error occours while trying to read the JSON
        if ($cacheData === null) {
            \KeyClass\Error::i10nErrorRegister('An error occured on trying to read the JSON file that controls cache', 'pack/sys');
        }

        $cacheDir = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."cache";
        if (!is_dir($cacheDir)){
            \KeyClass\FileTree::createDirectory($cacheDir, 777);
        }

        // If cache file did not exists, returns the value returned by the checkCache function (invalid)
        if (!file_exists(INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$cacheFileName)) {
            return $this->checkCache($cacheFileName, $cacheData);
        }

        // The view that will be the index of array is the first one who is detected
        // (because is the one who originate the request)
        $originalFileName = $viewConverted['viewsPath'][0];

        // If the cache control file exists
        if ($cacheData !== false) {
            // Checking if the cache file exists and is valid
            $cacheState=$this->checkCache($originalFileName, $cacheData);

            // If the registry of cache file did not exists or is invalid
            if ($cacheState['status'] === 'notexist' || $cacheState['status'] === 'invalid') {
                $data = [];
                $data[$originalFileName] = [
                    'cacheFileName' => $cacheFileName,
                    'route' => $route
                ];
            }

            // Setting the status of cache as valid to be recreated
            $statusCache='valid';
        }
        // If the cache controle file did not exists
        else {
            $data = [];

            // Storing cache data
            $data[$originalFileName] = [
                'cacheFileName' => $cacheFileName,
                'route' => $route
            ];

            // The cache status is valid, because the view will be mapped and
            // cached for the first time
            $statusCache='valid';
        }

        // If there is data to be stored
        if (isset($data)) {
            // Searching the modification date of the views and templates
            $viewsData = [];
            $templatesData = [];

            // For each view
            foreach($viewConverted['viewsPath'] as $vP) {
                // Searching for the modification date of the file
                $dateModify = filemtime(INSTALL_DIR.DIRECTORY_SEPARATOR.$vP);

                // Inserting in the data array
                $viewsData[$vP]=$dateModify;
            }
            
            // For each template
            foreach($viewConverted['templatesPath'] as $tP) {
                // Searching the modification date of the file
                $dateModify = filemtime($tP);

                // Inserting in the data array
                $templatesData[$tP]=$dateModify;
            }

            // Inserting in the end of data array
            $data[$originalFileName]['views']=$viewsData;
            $data[$originalFileName]['templates']=$templatesData;

            // Replacing/inserting the data
            $cacheData[$originalFileName]=$data[$originalFileName];

            // Recording the view cache configuration
            $write = \KeyClass\JSON::setJSONDataFile($data, $viewsCacheControlFile, true);
            if ($write !== true) {
                \KeyClass\Error::i10nErrorRegister('An error occured on trying to write the cache file of views', 'pack/sys');
            }
        }

        // Returning the status of cache
        return array(
            'status' => $statusCache
        );
    }

    /**
        Function to verify cache of a file
      
        @author Marcello Costa

        @package KeyClass\Controller
     
        @param  string  $originalFileName    View file that originated the cache
        @param  array   $cacheData           JSON cache file previously read
     
        @return array  Returns the data about the cache file
   */
    private function checkCache(string $originalFileName, array &$cacheData = null) : array {
        // If cache status is false = The cache file was not created yet
        // If cache status is null = The cache file was not read yet
        if ($cacheData === null) {
            $cacheData = \KeyClass\JSON::getJSONDataFile(INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR."views_cache_control.json");

            // If still equals to null, an error occurs on JSON read
            if ($cacheData === null) {
                \KeyClass\Error::i10nErrorRegister('An error occured on trying to read the JSON file that controls cache', 'pack/sys');
            }
        }

        // If the file is in cache
        if ($cacheData !== false && array_key_exists($originalFileName, $cacheData)) {
            // Cache status (initialized as valid)
            $status = 'valid';

            // For each file in view, checks if the file has modifications
            foreach($cacheData[$originalFileName]['views'] as $viewFilePath => $dateModifyCache) {
                // Searching for the modification date of the file
                $dateModifyNow = filemtime(INSTALL_DIR.DIRECTORY_SEPARATOR.$viewFilePath);

                // If something was modified after the cache is builded
                if ($dateModifyNow !== $dateModifyCache) {
                    $status='invalid';
                }
            }

            // For each template file, checks if the file has modifications
            foreach($cacheData[$originalFileName]['templates'] as $templateFilePath => $dateModifyCache) {
                // Searching for the modification date of the file
                $dateModifyNow = filemtime($templateFilePath);

                // If something was modified after the cache is builded
                if ($dateModifyNow !== $dateModifyCache) {
                    $status='invalid';
                }
            }

            // Returning the cache status
            return array(
                'status' => $status
            );
        }
        // If the file is not in cache
        else {
            return array(
                'status' => 'notexist'
            );
        }
    }

    /**
        Auxiliary function to create the cache file to be render
     
        @author Marcello Costa

        @package KeyClass\Controller
           
        @param  string  $fileName    Name of the file to be created (without extension)
     
        @return array  Processed data on views and related templates
    */
    protected function auxProcessViewCode(string &$fileName) : array {
        // Deleting old cache file
        if (file_exists(INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$fileName.".php")) {
            \KeyClass\FileTree::deleteFile(INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$fileName.".php");
        }

        // Convert template code + view into one php file
        $temp=new \Sagacious\SgsTemplate();
        $codev=$temp->convertSGV2PHP($this->SgsView);

        // Writing to file
        \KeyClass\FileTree::fileWriteContent(INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$fileName.".php", $codev['renderCode']);

        // Returning data from the file that was created
        return $codev;
    }

    /**
        Function to set a value inside the viewbag
     
        @author Marcello Costa

        @package KeyClass\Controller
      
        @param  mixed  $value    Value to be added to the viewbag
        @param  mixed  $key      Key that will point to the specified value
     
        @return void Without return
    */
    protected function addViewBag($value, $key=null) : void {
        global $kernelspace;
        $viewBag = $kernelspace->getVariable('viewBag', 'insiderFrameworkSystem');

        // Adding a value without a key
        if ($key === null) {
            $viewBag[]=$value;
        }

        // Adding a value with a key
        else {
            $viewBag[$key]=$value;
        }
        $kernelspace->setVariable(array('viewBag' => $viewBag), 'insiderFrameworkSystem');
    }

    /**
        Erases all content of the viewbag
     
        @author Marcello Costa
     
        @package KeyClass\Controller
      
        @return void  Without return
    */
    protected function eraseAllViewBag() : void {
        global $kernelspace;
        $kernelspace->setVariable(array('viewBag' => null), 'insiderFrameworkSystem');
    }

    /**
        Erases some content of the viewbag by the key
     
        @author Marcello Costa
     
        @package KeyClass\Controller
      
        @param  string  $key    Key of value that will be erased
      
        @return void  Without return
    */
    protected function eraseViewBagValue($key) : void {
        global $kernelspace;
        $viewBag = $kernelspace->getVariable('viewBag', 'insiderFrameworkSystem');
        if (isset($viewBag[$key])) {
            unset($viewBag[$key]);
        }
        $kernelspace->setVariable(array('viewBag' => $viewBag), 'insiderFrameworkSystem');
    }

    /**
        Alias method to modify the properties of an component

        @author Marcello Costa

        @package KeyClass\Controller
           
        @param  int     $id       Component ID
        @param  string  $state    State of component to be modified
        @param  array   $props    Properties to be modified in the component
     
        @return void
    */
    protected function changeComponentProps(int $id, string $state, array $props) : void {
        // Calling the function how modifies the states of the component in componentsBag
        $this->componentsBag->changeComponentProps($id, $state, $props, $this);
    }

    /**
        Convert an string received in the request

        @author Marcello Costa

        @package KeyClass\Controller
           
        @param  string  $data         Data received in request
        @param  bool    $origjson     If the data received are JSON
        @param  bool    $returnarray  If the return must be an array
     
        @return  mixed  Unknown type of return
    */
    protected function convertDataOfPost(string $data, bool $origjson=true, bool $returnarray=true) {
        // If it's an JSON
        if ($origjson) {
            $newdata=str_replace("\/", "/", $data);

            // If the data converted are an valid JSON
            if (\KeyClass\JSON::isJSON($newdata)) {
                // If the return it's an array
                if ($returnarray === true) {
                    return (json_decode($newdata, true));
                }

                // If the return it's not an array
                else {
                    return (json_decode($newdata));
                }
            }
            // If the data converted are not an valid JSON
            else {
                return false;
            }
        }

        // If it's not an JSON
        else {
            $newdata=str_replace("\/", "/", $data);
            return $newdata;
        }
    }

    /**
        Return an JSON as an response of controller (method)
      
        @author Marcello Costa

        @package KeyClass\Controller
     
        @param  mixed  $data          Data to be returned
        @param  int    $responseCode  Response code
     
        @return void
    */
    protected function responseJSON($data, int $responseCode = null) : void {
        if ($responseCode !== null) {
            http_response_code($responseCode);
        }
        header('Content-Type: application/json');
        echo \KeyClass\JSON::jsonEncodePrivateObject($data);
    }

    /**
        Return an XML as an response of controller (method)

        @author Marcello Costa

        @package KeyClass\Controller
     
        @param  mixed         $data            Data to be returned
        @param  bool          $fixNumericKeys  If true, set an preffix to the numeric keys of XML
     
        @param  int    $responseCode  Response code
     
        @return void
    */
    protected function responseXML($data, bool $fixNumericKeys=true, int $responseCode = null) : void {
        header('Content-type: text/xml');

        if ($fixNumericKeys === null) {
            $fixNumericKeys = false;
        }

        // Array
        if (is_array($data)) {
            $xmlObj = "";
            $xml=\KeyClass\XML::arrayToXML($data, $xmlObj, $fixNumericKeys);
            echo $xml->asXML();
        }

        // String
        else if (is_string($data) || (is_int($data)) || is_float($data) || is_bool($data))  {
            $xml = new \SimpleXMLElement('<root/>');
            $xml->addChild("root", $data);
            echo $xml->asXML();
        }

        // Object
        else if (is_object($data)) {
            $data=\KeyClass\Code::objectToArray($data);
            $this->responseXML($data);
        }

        else if (is_resource($data)) {
            \KeyClass\Error::i10nErrorRegister('It is not possible to convert a resource into an XML', 'pack/sys');
        }

        else if ($data === NULL) {
            $xml = new \SimpleXMLElement('<root/>');
            $xml->addChild("root", "");
            echo $xml->asXML();
        }

        // Invalid/unknow type
        else {
            \KeyClass\Error::i10nErrorRegister('It is not possible to convert a resource into an XML', 'pack/sys');
        }
    }

    /**
        Return an API response for the action of controller (method)

        @author Marcello Costa
        @package KeyClass\Controller      
     
        @param  mixed  $data       Data to be returned
        @param  array  $arrayArgs  Additional arguments to the function
     
        @return void
    */
    protected function responseAPI($data, array $arrayArgs=[]) : void {
        global $kernelspace;
        $debugBacktrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        // If some error occours
        if (
            isset($debugBacktrace[0]) && 
            isset($debugBacktrace[0]["object"]) &&
            isset($debugBacktrace[0]["args"]) &&
            get_class($debugBacktrace[0]["object"]) === "Controllers\sys\Error_Controller"
           ) {
             
           $controllerClass = NULL;
           $origFunction = NULL;
            
           if (DEBUG) {
             $data = array( 
               "error" => $debugBacktrace
             );
           }
           else {
             $data = $debugBacktrace[0]["args"][0];
           }
        }
        
        // Everything Ok!
        else {          
          $controllerClass=explode("\\", $debugBacktrace[1]["class"]);
          if (count($controllerClass) >= 3) {
              $controllerClass=$controllerClass[2];
          }
          else {
              $controllerClass=$controllerClass[1];
          }

          $controller=strtolower(str_replace("_Controller", "", $controllerClass));

          // Original function who fired the response
          $origFunction=$debugBacktrace[1]['function'];
        }

        if (\Helpers\globalHelper::existAndIsNotEmpty($arrayArgs, 'responseFormat')){
            $responseFormat=$arrayArgs['responseFormat'];
        }
        else{
            $responseFormat = $kernelspace->getVariable('responseFormat', 'insiderFrameworkSystem');
            if ($responseFormat === "") {   
              $responseFormat = DEFAULT_RESPONSE_FORMAT;
              $kernelspace->setVariable(array('responseFormat' => $responseFormat), 'insiderFrameworkSystem');
            }
        }
        
        switch($responseFormat) {
            case "JSON":
                $responseCode = null;
                if (is_array($arrayArgs) && count($arrayArgs) > 0) {
                    $arrayArgs=array_change_key_case($arrayArgs, CASE_LOWER);
                    if (isset($arrayArgs['responsecode'])) {
                        $responseCode = intval($arrayArgs['responsecode']);
                    }
                }
                $this->responseJSON($data, $responseCode);
            break;
            
            case "XML":
                $fixNumericKeys=true;
                $responseCode = null;
                if (is_array($arrayArgs) && count($arrayArgs) > 0) {
                    $arrayArgs=array_change_key_case($arrayArgs, CASE_LOWER);
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
                \KeyClass\Error::i10nErrorRegister('Invalid response type for API: %'.$responseFormat.'%', 'pack/sys');
            break;
        }
    }
    
    /**
        Serve files with http

        @author Marcello Costa

        @package Core

        @param  string  $originalFilePath  File path of the file to be served
        @param  string  $serveFileName     File name of the file to be served
        @param  string  $contentType       Type of file
        @param  float   $downloadRate      Download rate for download. When specified 0, no limit will be applied

        @return string  Binary representing file
    */
    public function serveFile(string $originalFilePath, string $serveFileName, string $contentType = "application/octet-stream", float $downloadRate = 0){
        if (file_exists($originalFilePath) && is_file($originalFilePath)) {
            // Setting headers
            header('Content-Type: '.$contentType);
            header('Content-Disposition: filename=' . $originalFilePath);
            header('Cache-control: private');
            header('Content-Length: ' . filesize($originalFilePath));

            // Opening the file
            $file = fopen($originalFilePath, "r");
            print fread($file, filesize($originalFilePath));
                flush();
            fclose($file);
        } else {
            \KeyClass\Error::errorRegister('Error: The file ' . $originalFilePath . ' does not exist!');
        }
    }
}
