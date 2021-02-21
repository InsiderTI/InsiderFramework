<?php
declare(strict_types=1);
namespace Modules\Insiderframework\Core;

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
    * @package Modules\Insiderframework\Core\Bootstrap\BootstrapTrait
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
    * @package Modules\Insiderframework\Core\Bootstrap\BootstrapTrait
    *
    * @return void
    */
    public static function initializeFramework(): void
    {
        Bootstrap::requireAndLoadAutoLoader();
        
        \Modules\Insiderframework\Core\KernelSpace::setVariable(array('FRAMEWORK_LOAD_STATUS' => 'LOADING'), 'insiderFrameworkSystem');

        Loaders\ConfigLoader::initializeConfigVariablesFromConfigFiles();

        \Modules\Insiderframework\Core\KernelSpace::setVariable(array('FRAMEWORK_LOAD_STATUS' => 'LOADED'), 'insiderFrameworkSystem');
    }
}