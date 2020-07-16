<?php

namespace Apps\Start\Controllers;

use Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag;
use Modules\InsiderFramework\Core\KernelSpace;

/**
 * Main Controller
 *
 * @author Marcello Costa
 * @package Apps\Start\Controllers\MainController
 *
 * @Route(path="/", defaultaction="home")
 * @Verbs(POST,GET)
 */
class MainController extends \Modules\InsiderFramework\Core\Controller
{
    /**
     * Home
     *
     * @author Marcello Costa
     * @package Apps\Start\Controllers\MainController
     *
     * @Route(path="home")
     * @Cache(none)
     *
     * @return void
     */
    public function home(): void
    {
        \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
            'Teste',
            "ATTACK_DETECTED"
        );

        /*
        friendlyAttackErrorMsg/
        friendlyCriticalErrorMsg/
        friendlyStandardErrorMsg/
        */
        //\Modules\InsiderFramework\Core\RoutingSystem\Request::requestRoute("/error/cookieError");
        
        SgsViewsBag::set('test', 123);
        $this->renderView('Start::home.sgv');
    }
}
