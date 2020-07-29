<?php

namespace Modules\InsiderFramework\Sagacious\Lib;

use Modules\InsiderFramework\Core\Aggregation;

/**
 * Class that defines the states of the components
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates
 */
class SgsComponentStates
{
    private $states = [];
    private $defaultStateId = "";
    private $currentState = "";
    private $componentId;
    private $app;

    /**
     * Construction method of component states
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates
     *
     * @param string $componentId Component Id
     * @param string $app         App name
     *
     * @return void
     */
    public function __construct(string $componentId, string $app)
    {
        $this->componentId = $componentId;
        $this->app = $app;

        $regcomponentfile = \Modules\InsiderFramework\Core\Json::getJSONDataFile(
            INSTALL_DIR . DIRECTORY_SEPARATOR . 'Apps' . DIRECTORY_SEPARATOR .
            $app . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR .
            'componentState.json'
        );
        
        if (is_array($regcomponentfile)) {
            \Modules\InsiderFramework\Core\Manipulation\Aggregation::changeKeysToLowerCaseArray($regcomponentfile);

            if (isset($regcomponentfile[$componentId])) {
                $componentConfig = $regcomponentfile[strtolower($componentId)];

                if (
                    !isset($componentConfig['states']) ||
                    empty($componentConfig['states'])
                ) {
                    $this->throwInvalidFile('States not found');
                } elseif (!isset($componentConfig['defaultstateid'])) {
                    $this->throwInvalidFile('defaultstateid not found');
                }

                foreach ($componentConfig['states'] as $stateId => $stateData) {
                    $this->changeStateData($stateId, $stateData);
                }

                $defaultStateId = $componentConfig['defaultstateid'];

                $this->setDefaultIdState($defaultStateId);
                $this->setCurrentState($defaultStateId);
            }
        }
    }

    /**
    * Get current state of component
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates
    *
    * @return array State data of component
    */
    public function getCurrentState(): array
    {
        return $this->getStateData($this->currentState);
    }

    /**
    * Set current properties of component
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates
    *
    * @param string $stateId ID of current state
    *
    * @return void
    */
    public function setCurrentState(string $stateId): void
    {
        if (!isset($this->states[$stateId])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                'State "' . $stateId . '" cannot be found in "' . get_class($this) . '"'
            );
        }

        $this->currentState = $stateId;
    }

    /**
    * Set default state
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates
    *
    * @param string $defaultStateId Default state id
    *
    * @return void
    */
    public function setDefaultIdState(string $defaultStateId): void
    {
        if (!isset($this->states[$defaultStateId])) {
            $this->throwInvalidFile("Default state $defaultStateId not found");
        }
        $this->defaultStateId = $defaultStateId;
    }

    /**
    * Get default state
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates
    *
    * @return void
    */
    public function getDefaultIdState(): string
    {
        return $this->defaultStateId;
    }

    /**
    * Get component states data
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates
    *
    * @param string $stateId State ID
    *
    * @return array Data of state
    */
    public function getStateData($stateId = null): array
    {
        if (!is_null($stateId)) {
            if (!isset($this->states[$stateId])) {
                return [];
            } else {
                return $this->states[$stateId];
            }
        } else {
            return $this->states;
        }
    }

    /**
    * Validate a state
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates
    *
    * @param array $state State to be validated
    *
    * @return void
    */
    public function validateState(array $state): void
    {
        if (!Aggregation::existAndIsNotEmpty($state, 'class')) {
            $this->throwInvalidFile('Class not found');
        }
    }

    /**
    * Add or update an state data
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates
    *
    * @param string $stateId   Id of state
    * @param array  $stateData Data of state
    *
    * @return void
    */
    public function changeStateData(string $stateId, array $stateData): void
    {
        $this->validateState($stateData);
        $this->states[$stateId] = $stateData;
    }

    /**
    * Removes a state
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates
    *
    * @param string $stateId State Id
    *
    * @return void
    */
    public function removeState(string $stateId): void
    {
        if (isset($this->states[$stateId])) {
            unset($this->states[$stateId]);
        }
    }

    /**
    * Thrown error "invalid file"
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsComponentStates
    *
    * @param string $message Detail of error message
    *
    * @return void
    */
    private function throwInvalidFile(string $message = null): void
    {
        \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
            "Cannot initialize SgsComponentStates $this->componentId " .
            "(app: $this->app): Invalid config file. Details: $message",
            "app/sys"
        );
    }
}
