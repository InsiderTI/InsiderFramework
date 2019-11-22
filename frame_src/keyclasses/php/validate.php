<?php
/**
  KeyClass\Validate
*/

namespace KeyClass;

/**
   KeyClass for validation

   @package KeyClass\Validate

   @author Marcello Costa
*/
class Validate{
    /**
        Checks if a string is an valid e-mail address
     
        @author Marcello Costa

        @package KeyClass\Validate
     
        @param  string  $email    E-mail to validated
      
        @return  bool  Validation result
    */
    public static function CheckEmail(string $email) : bool {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (strpos($email,'@') === false) {
                return false;
            }
            
            $pattern_alternative_email="^([\p{L}\.\-\d]+)@([\p{L}\-\.\d]+)((\.(\p{L}) {2,63})+)$";

            if (!(preg_match($pattern_alternative_email, $email))) {
                return false;
            }
        }

        return true;
    }

    /**
        Returns a numeric variable
     
        @author Marcello Costa

        @package KeyClass\Validate
     
        @param  string  $value    String to be tested
      
        @return  Int|Float  Returns the numeric variable
    */
    public static function getNumeric(string $value) {
        if (is_numeric($value)) {
          return $value+0;
        }
        
        return false;
    }
    
    /**
        Checks if exists or not special characters in a string
     
        @author Marcello Costa

        @package KeyClass\Code
     
        @param  string  $string    String to be tested
     
        @return  bool  Return true if special characters exists
    */
    public static function checkSpecialChars(string $string) : bool {
        if (!(preg_match("/^([a-zA-Z0-9]+)$/", $string))) {
            return true;
        }

        else {
            return false;
        }
    }
}
