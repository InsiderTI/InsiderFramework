<?php

namespace Modules\InsiderFramework\Sagacious\Lib;

use Modules\InsiderFramework\Sagacious\Lib\Interfaces\ISgsComponent;
use Modules\InsiderFramework\Core\Aggregation;
use Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates;

/**
 * Class that defines what an object is (components at the visualization level)
 * and what are the main functions that they all have
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponent
 */
class SgsComponent implements ISgsComponent
{
    /** @var string HTML code of the component */
    protected $code;

    /** @var SgsComponentStates States of the component */
    private $states;

    /**
     * Construct method
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponent
     *
     * @param string $componentId Component Id
     * @param string $app         App name
     *
     * @return void
     */
    public function __construct(string $componentId, string $app)
    {
        $this->states = new SgsComponentStates($componentId, $app);
        $this->initialize();
    }

    /**
    * Initialization method for the component that extends this class
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponent
    *
    * @return void
    */
    public function initialize(): void
    {
    }

    /**
     * Closes the tag code of the component (inserts a close tag in the HTML)
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponent
     *
     * @return string Returns the close tag
    */
    public function renderCloseTag(): string
    {
        return $this->tagclose;
    }

    /**
     * Returns the code of the component
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponent
     *
     * @return mixed Code of the component
     */
    public function rawComponent()
    {
        return $this->code;
    }

    /**
     * Echos the code of the component
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponent
     *
     * @return void
     */
    public function renderComponent(): void
    {
        // Se o código do componente não pode ser exibido
        if (!is_string($this->code) && !is_null($this->code) && !is_numeric($this->code)) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'An component has a code that can not be displayed by the echo: %' .
                json_encode($backtrace) . '%',
                "app/sys",
                LINGUAS,
                'LOG'
            );
        }
        echo $this->code;
    }

    /**
    * Get states of component
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponent
    *
    * @return SgsComponentStates States of component
    */
    public function getStates(): SgsComponentStates
    {
        return $this->states;
    }
}
