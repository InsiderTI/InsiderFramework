<?php
declare(strict_types=1);
namespace Modules\Insiderframework\Core;

use \Modules\InsiderFramework\Core\Loaders\ConfigFiles;

/**
 * Class for the framework bootstrap functions
 *
 * @author Marcello Costa
 *
 * @package Modules\Insiderframework\Core\Bootstrap
 */
class Bootstrap
{
  /**
    * Require and load the autoloader
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Bootstrap
    *
    * @return void
    */
    protected static function requireAndLoadAutoLoader(): void
    {
      require(
        'Modules'.DIRECTORY_SEPARATOR.
        'Insiderframework'.DIRECTORY_SEPARATOR.
        'Core'.DIRECTORY_SEPARATOR.
        'Loaders'.DIRECTORY_SEPARATOR.
        'Autoloader.php'
      );
      \Modules\Insiderframework\Core\Loaders\AutoLoader::initializeAutoLoader();
    }

  /**
    * Initializes framework variables and enviroment classes
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Bootstrap
    *
    * @return void
    */
    public static function initializeFramework(): void
    {
        Bootstrap::requireAndLoadAutoLoader();
        KernelSpace::setVariable(array('FRAMEWORK_LOAD_STATUS' => 'LOADING'), 'insiderFrameworkSystem');
        
        //ConfigFiles::initializeConfigVariablesFromConfigFiles();

        KernelSpace::setVariable(array('FRAMEWORK_LOAD_STATUS' => 'LOADED'), 'insiderFrameworkSystem');
    }
}