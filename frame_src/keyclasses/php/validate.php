<?php
/**
  Arquivo KeyClass\Validate
*/

// Namespace das KeyClass
namespace KeyClass;

/**
   KeyClass de validação

   @package KeyClass\Validate

   @author Marcello Costa
*/
class Validate{
    /**
        Função que verifica se um email é válido
     
        @author Marcello Costa

        @package KeyClass\Validate
     
        @param  string  $email    Email a ser validado
      
        @return  bool  Resultado da verificação
    */
    public static function CheckEmail(string $email) : bool {
        // Se o email é inválido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Se o email não contiver arroba
            if (strpos($email,'@') === false) {
                return false;
            }
            
            // Talvez o email tenha um endereço "non-standard", testando
            $pattern_alternative_email="^([\p{L}\.\-\d]+)@([\p{L}\-\.\d]+)((\.(\p{L}) {2,63})+)$";

            if (!(preg_match($pattern_alternative_email, $email))) {
                // Se mesmo assim der erro, então é um email inválido
                return false;
            }
        }
        
        // Email válido
        return true;
    }

    /**
        Função que retorna um numeral
     
        @author Marcello Costa

        @package KeyClass\Validate
     
        @param  string  $value    String a ser testada
      
        @return  Int|Float  Retorna numeral
    */
    public static function getNumeric(string $value) {
        if (is_numeric($value)) {
          return $value+0;
        }
        
        return false;
    }
    
    /**
        Função que retorna se existem ou não caracteres especias
        numa string
     
        @author Marcello Costa

        @package KeyClass\Code
     
        @param  string  $string    String a ser testada
     
        @return  bool  Retorna true se existirem caracteres especiais
    */
    public static function checkSpecialChars(string $string) : bool {
        // Verifica se não existem caracteres especiais
        if (!(preg_match("/^([a-zA-Z0-9]+)$/", $string))) {
            // Se existirem caracteres especiais
            return true;
        }

        // Não existem caracteres especiais
        else {
            return false;
        }
    }
}