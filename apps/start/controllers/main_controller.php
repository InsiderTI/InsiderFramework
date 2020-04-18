<?php

namespace Controllers\start;

use Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag;
use Modules\InsiderFramework\Core\KernelSpace;

/**
 * Main Controller
 *
 * @author Marcello Costa
 * @package Controllers\start\MainController
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
   * @package Controllers\start\MainController
   *
   * @Route(path="home")
   * @Cache(none)
   *
   * @return void
   */
    public function home(): void
    {
        SgsViewsBag::set('test', 123);

        // Renderview
        $this->renderView('start::home.sgv');
    }
}
