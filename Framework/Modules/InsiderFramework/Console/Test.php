<?php

namespace Modules\InsiderFramework\Console;

use Modules\InsiderFramework\Console\Adapter as ConsoleAdapter;

/**
 * Test main class
 *
 * @author Marcello Costa
 *
 * @package \Modules\InsiderFramework\Console\Test
 */
class Test
{
    /**
    * Method description
    *
    * @author Marcello Costa
    *
    * @package \Modules\InsiderFramework\Console\Test
    *
    * @param Modules/InsiderFramework/Console/Adapter $console Console object
    *
    * @return void
    */
    public static function run(\Modules\InsiderFramework\Console\Adapter $console)
    {
        $target = $console->getArgument('target');

        // If it's a class
        if (class_exists($target)) {
            /*
            $testClass = new $target();
            var_dump($testClass);
            die("FILE: " . __FILE__ . "<br/>LINE: " . __LINE__);
            */

            $suite = new \PHPUnit\Framework\TestSuite();
            $suite->addTestSuite($target);

            var_dump($suite);
            die("FILE: " . __FILE__ . "<br/>LINE: " . __LINE__);

            $testRunner = new \PHPUnit\TextUI\TestRunner();
            $testRunner::run($suite);
        }

        // Otherwise maybe it's a directory
        if (is_dir($target)) {
            die('2');
        }

        \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
            'Class or directory not found for testing: ' . $target
        );
    }
}
