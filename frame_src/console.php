<?php

/**
  This file can be executed on terminal. This is the main file of console in
  framework. Exists in this file functions to manage packages, create
  packs, controlers, etc.

  @author Marcello Costa
  @package Core
 */
list($tmpBasePath) = get_included_files();
$basePath = dirname($tmpBasePath);
unset($tmpBasePath);
chdir($basePath);

// Initializing framework
require_once($basePath . DIRECTORY_SEPARATOR . 'init.php');

/**
  @global array Variable used by framework to control requests that are maked by console

  @package Core
 */
$kernelspace->setVariable(array('consoleRequest' => true), 'insiderFrameworkSystem');

// Requiring insiderconsole module
require_once($basePath . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'insiderconsole' . DIRECTORY_SEPARATOR . 'loader.php');

// If help is requested
if ($climate->arguments->defined('help')) {
    $climate->usage();
    $climate->br();
    die();
}

// Checking if some action is defined by user
$definedAction=[];
foreach($consoleDefaultActions as $actionK => $values){
    if (($climate->arguments->defined($actionK))) {
        $definedAction[]=$actionK;
    }
}

// Too many actions
if (count($definedAction) > 1){
    $climate->to('error')->red("Syntax error: Too many actions specified")->br();
    $climate->to('error')->write("Type <light_blue>console.php --help</light_blue> for help")->br();
    die();
}

// No action
if (count($definedAction) == 0){
    $climate->usage();
    $climate->br();
    die();
}

// Manage action
Modules\insiderconsole\actionManager::manageAction($climate, $definedAction[0]);