<?php
/**
  KeyClass\Kernelspace
*/

namespace KeyClass;

/**
  KeyClass of kernelspace
  
  @author Marcello Costa

  @package KeyClass
*/
class KernelSpace{
    /** @var array Array kernelspace */
    private $kernelspace;
    
    /**
        Sets a variable inside a context
     
        @author Marcello Costa

        @package KeyClass\KernelSpace

        @param  array  $variable    Variable to be inserted inside the context
        @param  string $context     Context where the variable will be putted in
     
        @return bool Processing result
    */
    public function setVariable(array $variable, string $context = "global") : bool {
        // Checking if the context did already exists
        if (!isset($this->kernelspace[$context])){
            $this->kernelspace[$context]=[];
        }

        // Putting the variable in the context
        $this->kernelspace[$context] = array_merge($this->kernelspace[$context], $variable);
        
        return true;
    }
    
    /**
        Gets the value of a variable which is inside a context
     
        @author Marcello Costa

        @package KeyClass\KernelSpace

        @param  array  $variableName    Name of the variable
        @param  string $context         Context where the variable belongs
     
        @return mixed Value of the variable or null (if the variable did not exists)
    */
    public function getVariable(string $variableName, string $context = "global"){
        // Verificando se o contexto já existe
        if (!isset($this->kernelspace[$context])){
            \KeyClass\Error::i10nErrorRegister("Context not found in kernelspace %".$context."%", "pack/sys");
        }

        // Returning the value of the variable
        if (isset($this->kernelspace[$context][$variableName])){
            return $this->kernelspace[$context][$variableName];
        }
        return null;
    }
}
