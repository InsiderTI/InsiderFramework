<?php
/**
  Arquivo KeyClass\Kernelspace
*/

// Namespace das KeyClass
namespace KeyClass;

/**
  KeyClass de kernelspace
  
  @author Marcello Costa

  @package KeyClass
*/
class KernelSpace{
    /** @var array Array kernelspace */
    private $kernelspace;
    
    /**
        Função que seta uma variável em um contexto
     
        @author Marcello Costa

        @package KeyClass\KernelSpace

        @param  array  $variable    Variável a ser inserida no contexto
        @param  string $context     Contexto em que está sendo inserida a variável
     
        @return bool Retorno da operação
    */
    public function setVariable(array $variable, string $context = "global") : bool {
        // Verificando se o contexto já existe
        if (!isset($this->kernelspace[$context])){
            $this->kernelspace[$context]=[];
        }
        
        // Gravando a variável no contexto
        $this->kernelspace[$context] = array_merge($this->kernelspace[$context], $variable);
        
        return true;
    }
    
    /**
        Função que recupera o valor de uma variável em um contexto
     
        @author Marcello Costa

        @package KeyClass\KernelSpace

        @param  array  $variableName    Nome da variável
        @param  string $context         Contexto em que está sendo inserida a variável
     
        @return mixed Valor da variável ou nulo (caso não exista)
    */
    public function getVariable(string $variableName, string $context = "global"){
        // Verificando se o contexto já existe
        if (!isset($this->kernelspace[$context])){
            \KeyClass\Error::i10nErrorRegister("Context not found in kernelspace %".$context."%", "pack/sys");
        }

        // Retornando o valor da variável no contexto
        if (isset($this->kernelspace[$context][$variableName])){
            return $this->kernelspace[$context][$variableName];
        }
        return null;
    }
}
