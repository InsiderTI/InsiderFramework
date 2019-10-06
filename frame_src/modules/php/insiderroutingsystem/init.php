<?php

global $kernelspace;

$routingConfig = \KeyClass\JSON::getJSONDataFile(INSTALL_DIR . DIRECTORY_SEPARATOR . 'frame_src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'insiderRoutingSystemConfig.json');
if(!is_array($routingConfig) || !isset($routingConfig['settings']) || !isset($routingConfig['actions'])){
    primaryError('Error reading insiderRoutingSystemConfig file');
}

if (!isset($routingConfig['settings']) || 
    !isset($routingConfig['settings']['routeCaseSensitive']) ||
    !is_bool($routingConfig['settings']['routeCaseSensitive'])){
    primaryError('Error reading settings from insiderRoutingSystemConfig');
}
$kernelspace->setVariable(array('routingSettings' => $routingConfig['settings']), 'insiderRoutingSystem');
$kernelspace->setVariable(array('routingActions' => $routingConfig['actions']), 'insiderRoutingSystem');

$defaultActions = $routingConfig['actions'];
// Verificando cada uma das default actions
foreach($defaultActions as $daK => $dA){
    if (
        (!isset($dA['pack']) || trim($dA['pack']) === "") ||
        (!isset($dA['controller']) || trim($dA['controller']) === "") ||
        (!isset($dA['route']) || trim($dA['route']) === "") ||
        (!isset($dA['method']) || trim($dA['method']) === "") ||
        (!isset($dA['responsecode']) || (int)($dA['responsecode']) === 0)
       ){
        primaryError("Default action '".$daK."' not configured correctly");
    }
}
if (isset($daK)){
    unset($daK);
    unset($dA);
}

// Validando se a rota 404 está configurada
if (!isset($defaultActions['NotFound'])){
    primaryError("Default action 'NotFound' not configured in defaultActions");
}
if (!isset($defaultActions['NotAuth'])){
    primaryError("Default action 'NotAuth' not configured in defaultActions");
}

// Verificando a configuração de erro
if (!isset($defaultActions['CriticalError'])) {
    primaryError("Default action 'CriticalError' not configured in defaultActions");
}

$kernelspace->setVariable(array('defaultActions' => $defaultActions), 'insiderRoutingSystem');
unset($defaultActions);

// Routing Object
$read=new \Modules\insiderRoutingSystem\Read();

// Reading the routes
$read->ReadControllerRoutes();

unset($read);