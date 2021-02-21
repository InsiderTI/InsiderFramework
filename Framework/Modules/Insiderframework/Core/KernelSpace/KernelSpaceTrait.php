<?php

namespace Modules\Insiderframework\Core\KernelSpace;

/**
 * Methods responsible for handle kernelspace
 *
 * @author Marcello Costa
 *
 * @package Modules\Insiderframework\Core\KernelSpace\KernelSpaceTrait
 */
trait KernelSpaceTrait
{
    /**
     * Sets a variable inside a context
     *
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\KernelSpace\KernelSpaceTrait
     *
     * @param array  $variable Variable to be inserted inside the context
     * @param string $context  Context where the variable will be putted in
     *
     * @return bool Processing result
     */
    public static function setVariable(array $variable, string $context = "global"): bool
    {
        global $kernelspace;

        // Checking if the context did already exists
        if (!isset($kernelspace[$context])) {
            $kernelspace[$context] = [];
        }

        // Putting the variable in the context
        $kernelspace[$context] = array_merge($kernelspace[$context], $variable);

        return true;
    }

    /**
     * Gets the value of a variable which is inside a context
     *
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\KernelSpace\KernelSpaceTrait
     *
     * @param array  $variableName Name of the variable
     * @param string $context      Context where the variable belongs
     *
     * @return mixed Value of the variable or null (if the variable did not exists)
     */
    public static function getVariable(string $variableName, string $context = "global")
    {
        global $kernelspace;
        if (isset($kernelspace[$context][$variableName])) {
            return $kernelspace[$context][$variableName];
        }

        return null;
    }
}
