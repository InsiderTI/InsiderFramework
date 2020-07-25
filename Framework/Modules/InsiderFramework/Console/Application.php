<?php

namespace Modules\InsiderFramework\Console;

use Modules\InsiderFramework\Console\Adapter as ConsoleAdapter;

/**
 * Main function of console.
 *
 * @author Marcello Costa
 */
class Application
{
    /**
    * Get a new console instance
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Application
    *
    * @return any Any type of console
    */
    public static function createConsoleInstance(): ConsoleAdapter
    {
        $console = new ConsoleAdapter();
        return $console;
    }

    /**
     * Manage command line request.
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Console\Application
     *
     * @param object $console Modules\InsiderFramework\Console\Adapter object
     *
     * @return void
     */
    public static function manageCommand(&$console): void
    {
        if ($console->getArgument('help')) {
            $console->usage();
            die();
        }

        if (!($console->getArgument('action'))) {
            $console->setOutput('error');
            $console->br();
            $console->setTextColor('red');
            $console->write('Syntax error');
            $console->br();
            $console->write('Type <light_blue>console.php --help</light_blue> for help');
            $console->br();

            $console->usage();
            die();
        }

        $action = $console->getArgument('action');

        $actionsAndParameters = \Modules\InsiderFramework\Console\Application::getActionsAndParameters();

        if (trim($action) === "" || !isset($actionsAndParameters['validActions'][strtolower($action)])) {
            if (trim($action) === "") {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister("Action not specified");
            }

            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister("Action '$action' not found");
        }

        $function = $actionsAndParameters['validActions'][strtolower($action)]['function'];
        $class = $actionsAndParameters['validActions'][strtolower($action)]['class'];

        if (!class_exists("$class") || !method_exists($class, $function)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                "Class or function did not exists '$class::$function'"
            );
        }

        call_user_func("$class::$function", $console);
    }

    /**
     * Initialize console environment
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Console\Application
     *
     * @param object $console Modules\InsiderFramework\Console\Adapter object
     *
     * @return void
     */
    public static function initialize(&$console): void
    {
        \Modules\InsiderFramework\Console\Application::loadOperations($console);
    }

    /**
     * Load possible actions and additional parameters for console operations.
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Console\Application
     *
     * @return array Array of actions and parameters
     */
    protected static function getActionsAndParameters()
    {
        $consoleRegistryDir = 'Modules' . DIRECTORY_SEPARATOR .
                              'InsiderFramework' . DIRECTORY_SEPARATOR .
                              'Console';

        $dataActions = \Modules\InsiderFramework\Core\Registry::getLocalConfigurationFile(
            $consoleRegistryDir . DIRECTORY_SEPARATOR .
            'Actions.json'
        );

        $dataAdditionalParameters = \Modules\InsiderFramework\Core\Registry::getLocalConfigurationFile(
            $consoleRegistryDir . DIRECTORY_SEPARATOR .
            'AdditionalParameters.json'
        );

        if (
            !isset($dataActions['actions']) ||
            !isset($dataAdditionalParameters['additionalParameters'])
        ) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                'Invalid actions/additionalParameters file of console'
            );
        }

        foreach ($dataActions['actions'] as $actionKey => $actionValue) {
            if (
                !isset($dataActions['actions'][$actionKey]['description'])
            ) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister('Invalid actions file of console');
            }

            $description = "\n\t\t" . implode("\n\t\t", $dataActions['actions'][$actionKey]['description']);
            $dataActions['actions'][$actionKey]['description'] = $description;
        }

        return array(
            'validActions' => $dataActions['actions'],
            'additionalParameters' => $dataAdditionalParameters['additionalParameters'],
        );
    }

    /**
     * Load possible operations on console.
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Console\Application
     *
     * @param object $console Modules\InsiderFramework\Console\Adapter object
     *
     * @return void
     */
    protected static function loadOperations(&$console): void
    {
        $actionsAndParameters = \Modules\InsiderFramework\Console\Application::getActionsAndParameters();

        $parsedActions = '';

        foreach ($actionsAndParameters['validActions'] as $validAction) {
            $parsedActions .= $validAction['description'] . "\n";
        }

        $helpArray = array(
            'help' => [
                'prefix' => 'h',
                'longPrefix' => 'help',
                'description' => 'Shows this help',
            ],
        );

        $actionsArray = array(
            'action' => [
                'prefix' => 'a',
                'longPrefix' => 'action',
                'description' => "Action to be executed. Valid actions are: \n" .
                                $parsedActions,
                'required' => true,
            ],
        );

        $arguments = array_merge(
            $helpArray,
            $actionsArray,
            $actionsAndParameters['additionalParameters']
        );

        $console->addArguments($arguments);
    }
}
