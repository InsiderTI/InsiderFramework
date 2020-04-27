<?php

namespace Modules\InsiderFramework\Core;

/**
* Fake class to avoid direct error in case of no package generate
* the RenderEngine class for controllers
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core
*/
class RenderEngine
{
    function __construct()
    {
        \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister('No render engine installed/detected');
    }
}
