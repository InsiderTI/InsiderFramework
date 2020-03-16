<?php
// Namespace relativo ao insiderconsole
namespace Modules\insiderconsole;

/*
  Classe de gerenciamento de ações do console
*/
class actionManager {
    /**
        Manage an action from the console

        @author Marcello Costa

        @package Modules\insiderconsole

        @param  object  $climate   Climate object
        @param  string  $action    Action from the console

        @return  mixed  Mixed returned
    */
    static public function manageAction(&$climate, string $action){
        $argumentsAndDependencies = actionManager::getArgumentsAndDependencies($climate, $action);

        switch (strtolower($action)) {
            case 'update':
            case 'install':
               \Modules\insiderconsole\PackageManager::installUpdatePackage($climate, $argumentsAndDependencies);
            break;

            case 'remove':
            case 'uninstall':
                actionManager::removePackage($climate, $argumentsAndDependencies);
            break;

            case 'generate':
                \Modules\insiderconsole\DirectoryTreeGenerator::generate($climate, $argumentsAndDependencies);
            break;
            
            case 'build':
                \Modules\insiderconsole\Build::buildPackage($climate, $argumentsAndDependencies);
            break;

            case 'test':
            case 'create':
                $climate->to('error')->red("----------------------------------------");
                $climate->to('error')->red("Console function $action not implemented yet!");
                $climate->to('error')->red("----------------------------------------");
            break;

            default:
                $climate->to('error')->red("----------------------------------------");
                $climate->to('error')->red('Action ' . $action . " not recognized");
                $climate->to('error')->red("----------------------------------------");
                die();
            break;
        }
    }

    /**
        Gets the arguments of the actions and validates if the user sent all 
        the necessary data. Returns all arguments and dependencies for the 
        action sent by the user.

        @author Marcello Costa

        @package Modules\insiderconsole

        @param  object  $climate    Climate object
        @param  string  $action     Action

        @return  array Data arguments
    */
    static public function getArgumentsAndDependencies(&$climate, string $action) : array {
        global $kernelspace;
        $climate->arguments->parse();
        $actionValue = $climate->arguments->get($action);
        
        $dataArguments = [];
        $dataArguments['actionName'] = $action;
        $dataArguments['actionValue'] = $actionValue;
        
        // If action not exists in consoleDefaultActions array
        if(!isset($kernelspace->getVariable('consoleDefaultActions', 'insiderFrameworkSystem')[$action])){
            \KeyClass\Error::errorRegister("Action '".$action."' did not exists in consoleDefaultActions array");
        }

        // Validating dependencies
        $actionData = $kernelspace->getVariable('consoleDefaultActions', 'insiderFrameworkSystem')[$action];
        if (\Helpers\globalHelper::existAndIsNotEmpty($actionData, 'depends')){
            foreach($actionData['depends'] as $dependency){
                $dependencyValue = $climate->arguments->get($dependency)."";
                if ($dependencyValue === ""){
                    \KeyClass\Error::errorRegister("Action '".$action."' needs '".$dependency."' argument");
                }
                $dataArguments['dependecies'][$dependency]=$dependencyValue;
            }
        }
        
        return $dataArguments;
    }
    
    /**
        Function thats process an update or install of package

        @author Marcello Costa

        @package Modules\insiderconsole

        @param  string  $tmpDir    Temporary directory
        @param  string  $message   Message of error

        @return  void  Without return
    */
    public static function stopInstallUpdate(string $tmpDir = null, string $message = null) : void {
        global $kernelspace;
        $climate = $kernelspace->getVariable('climate', 'insiderFrameworkSystem');

        if ($tmpDir !== null){
            // Erasing temporary directory
            \KeyClass\Filetree::deleteDirectory($tmpDir);
        }

        // Showing the error
        if ($message !== null){
            $climate->br();
            $climate->to('error')->red($message)->br();
        }

        // Stopping script
        die();
    }
}
