<?php

namespace Modules\InsiderFramework\Sagacious\Components\Html\Title;

use Modules\InsiderFramework\Sagacious\Lib\SgsComponent;
use Modules\InsiderFramework\Sagacious\Lib\SgsPage;

/**
 * Main class of the Title object (SgsComponent)
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Components\Html\Title
 */
class Title extends SgsComponent
{
    /**
     * Initialize code of the component
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Components\Html\Title
     *
     * @return void
     */
    public function initialize(): void
    {
        $stateData = $this->getStates()->getCurrentState();
        $props = $stateData['props'];

        foreach ($props as $p => $pval) {
            switch ($p) {
                case 'fixedtitle':
                    $fixedtitle = $pval;
                    break;

                case 'title':
                    $title = $pval;
                    break;
            }
        }

        if (!(isset($title))) {
            $titleall = $fixedtitle;
        } else {
            $titleall = $fixedtitle . " - " . $title;
        }

        $js = "<script>
                document.title = '" . $titleall . "';
             </script>";

        $KcPage = new SgsPage();
        \Modules\InsiderFramework\Sagacious\Lib\SgsPage::updateJsOfPage($js);

        $this->code = "<title>" . $titleall . "</title>";
    }
}
