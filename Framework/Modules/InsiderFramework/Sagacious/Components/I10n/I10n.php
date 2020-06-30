<?php

namespace Modules\InsiderFramework\Sagacious\Components\I10n;

use Modules\InsiderFramework\Sagacious\Lib\SgsComponent;
use Modules\InsiderFramework\Core\KernelSpace;

/**
 * Main class of component I10n (SgsComponent)
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Components\I10n
 */
class I10n extends SgsComponent
{
    /**
     * Initialize code of the component
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Components\I10n
     *
     * @return void
     */
    public function initialize(): void
    {
        $stateData = $this->getStates()->getCurrentState();
        $props = $stateData['props'];

        if (!isset($props['message'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister('I10n component need a messageid');
        }
        $message = $props['message'];

        if (!isset($props['domain'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister('I10n component need a domain');
        }
        $domain = $props['domain'];

        $linguas = LINGUAS;
        if (isset($props['linguas'])) {
            $linguas = $props['linguas'];
        }

        $msgI10n = \Modules\InsiderFramework\Core\Manipulation\I10n::getTranslate(
            $message,
            $domain,
            $linguas
        );

        if ($msgI10n === "") {
            $msgI10n = str_replace("%", "", $message);
        }

        $this->code = $msgI10n;
    }
}
