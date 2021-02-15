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
    * @package Modules\Insiderframework\Core\Bootstrap
    *
    * @return void
    */
    protected static function requireAndLoadAutoLoader(): void
    {
      require('Modules/Insiderframework/Core/Loaders/Autoloader.php');
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
        KernelSpace::setVariable(array('FRAMEWORK_LOAD_STATUS' => 'LOADED'), 'insiderFrameworkSystem');
    }
}