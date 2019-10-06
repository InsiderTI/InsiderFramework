<?php

    // Silence always comes with a price
    // Starts the Framework
    require_once('../frame_src/init.php');

    // Global GET variable
    global $kernelspace;

    // Check CPU usage
    \KeyClass\System::checkCpuAvg();

    // Route that redirects the user
    // If class of native routing system exists, call it
    if (class_exists('\Modules\insiderRoutingSystem\Request')) {
        \Modules\insiderRoutingSystem\Request::requestRoute(
                isset($kernelspace->getVariable('GET', 'insiderFrameworkSystem')['url']) ? $kernelspace->getVariable('GET', 'insiderFrameworkSystem')['url'] : "/"
            );
    }
    else{
        // If class of another routing system exists, call it
        if (class_exists('\Modules\RoutingSystem\Request')) {
            \Modules\RoutingSystem\Request::requestRoute(
                    isset($kernelspace->getVariable('GET', 'insiderFrameworkSystem')['url']) ? $kernelspace->getVariable('GET', 'insiderFrameworkSystem')['url'] : "/"
                );
        }
    }
