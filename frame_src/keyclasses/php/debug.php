<?php
/**
  KeyClass\Debug
*/

// Namespace of KeyClass
namespace KeyClass;

/**
  KeyClass for debugging the code
  
  @package KeyClass\Debug

  @author Marcello Costa
*/
class Debug{
    /**
      Shows the debugBar
    
      @author Marcello Costa

      @package KeyClass\Debug

      @param  string  $action    Action to be fired ("count" or "render")
    
      @return void
    */
    public function debugBar(string $action) : void {
        global $kernelspace;
        
        // Require file manually
        require_once(INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."keyclasses".DIRECTORY_SEPARATOR."php".DIRECTORY_SEPARATOR."security.php");

        switch($action) {
            // If it's the beggining of counting 
            case "count":
                // Setting the cookie who marks the start of the script
                // (if it's not already initialized)
                $startTest=\KeyClass\Security::getCookie('starttime');
                if ($startTest == '0' || $startTest === false) {
                    \KeyClass\Security::setCookie('starttime', microtime());
                }
            break;

            // Stopping the counter and rendering
            case "render":
                // Recovering the info about the time of the request
                $starttime=floatval(\KeyClass\Security::getCookie('starttime'));

                // Initializing the variable which marks the end of the script
                $end = floatval(microtime());

                // Resetting the counter
                \KeyClass\Security::setCookie('starttime', 0);

                // Calculating how much time was spent and how much memory is used
                $elapsedTime = round(floatval($end) - floatval($starttime), 2);
                $memoryUsage = round(((memory_get_peak_usage(true) / 1024) / 1024), 2);

                // Maybe was not enough time to browser send the cookie
                if ($elapsedTime < 0) {
                  // Calculating how much time was spent and the memory usage starts in 0
                  $elapsedTime = round(floatval($end) - floatval(0), 2);
                  $memoryUsage = round(((memory_get_peak_usage(true) / 1024) / 1024), 2);
                }

                // Displaying an message in the page
                $msg='<div id="debugbarinsiderframe">';                    
                $msg.='<img src="'.REQUESTED_URL.'/favicon.png" id="debugbarinsiderframeimg"/> Processing time: <span style="color:#2C89A0;">'.$elapsedTime.'</span>s / Memory Usage: <span style="color:#FF0000;">'.$memoryUsage.'Mb</span>';
                $msg.='</div>';
                
                $kernelspace->setVariable(array(
                   'injectedHtml' => $kernelspace->getVariable('injectedHtml', 'insiderFrameworkSystem').$msg
                ), 'insiderFrameworkSystem');
             break;
        }
    }
}
