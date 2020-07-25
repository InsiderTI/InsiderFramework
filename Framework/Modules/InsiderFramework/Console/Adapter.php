<?php

namespace Modules\InsiderFramework\Console;

/**
 * Adapter class for a console instance
 *
 * @author Marcello Costa
 * @package Modules\InsiderFramework\Console\Adapter
 */
class Adapter
{
    private $consoleObj;
    private $currentTextColor = 'write';
    private $currentBackgroundColor = '';

    public function __construct()
    {
        $this->consoleObj = new \League\CLImate\CLImate();
    }

    /**
    * Print the usage text
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Adapter
    *
    * @return void
    */
    public function usage()
    {
        $this->consoleObj->usage();

        return $this;
    }

    /**
    * Print a break line
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Adapter
    *
    * @return void
    */
    public function br()
    {
        $this->consoleObj->br();

        return $this;
    }

    /**
    * Change the output text color
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Adapter
    *
    * @return void
    */
    public function setTextColor(string $color)
    {
        $this->currentTextColor = $color;

        return $this;
    }

    /**
    * Change the output background color
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Adapter
    *
    * @return void
    */
    public function setBackgroundColor(string $color)
    {
        $this->currentBackgroundColor = $color;

        return $this;
    }

    /**
    * Write a message to the default ouput
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Adapter
    *
    * @param string $message Message to be writted
    *
    * @return void
    */
    public function write(string $message): void
    {
        $this->consoleObj = call_user_func(
            array(
                $this->consoleObj,
                $this->currentTextColor
            )
        );

        if ($this->currentBackgroundColor !== "") {
            $this->consoleObj = call_user_func(
                array(
                    $this->consoleObj,
                    $this->currentBackgroundColor
                )
            );
        }

        $this->consoleObj->out($message);
    }

    /**
    * Change the output for console
    *
    * @author Marcello Costa
    * @package Modules\InsiderFramework\Console\Adapter
    *
    * @param string $output Output name
    *
    * @return void
    */
    public function setOutput(string $output)
    {
        switch (strtolower($output)) {
            case 'out':
                break;
            case 'error':
                break;
            case 'buffer':
                break;
            default:
                \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                    'Output not recognized for console'
                );
                break;
        }

        $this->consoleObj->defaultTo(strtolower($output));

        return $this;
    }

    /**
    * Get argument from command line parse
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Adapter
    *
    * @param string $argumentName Name of argument
    *
    * @return string Argument value or null (if did not exists)
    */
    public function getArgument(string $argumentName): ?string
    {
        if ($this->consoleObj->arguments->defined($argumentName)) {
            $this->consoleObj->arguments->parse();
            $argumentValue = $this->consoleObj->arguments->get($argumentName);
            return $argumentValue;
        }

        return null;
    }

    /**
    * Adds one or more arguments to be used on console
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Adapter
    *
    * @param array $arguments Array of arguments
    *
    * @return void
    */
    public function addArguments(array $arguments)
    {
        $this->consoleObj->arguments->add($arguments);

        return $this;
    }
}
