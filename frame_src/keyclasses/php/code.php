<?php
/**
  KeyClass\Code
*/

// Namespace of KeyClass
namespace KeyClass;

require_once('frame_src'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'Sagacious'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'template.php');

/**
  Code processing KeyClass
  
  @author Marcello Costa

  @package KeyClass
*/
class Code{
    /**
        Function thats makes a case insensitive search for a key inside an array
        
        @author Marcello Costa
     
        @package KeyClass\Code

        @param  string  $name    Name of the key
        @param  array   $array   Target array of the search
     
        @return  bool  If key exists or not
    */
    public static function arrayKeyExistsCaseInsensitive(string $name, array $array) : bool {
        // Converting to lowercase
        $name=strtolower($name);

        $keys=array_keys($array);
        $map=array();
        foreach($keys as $key)
        {
            $map[strtolower($key)]=$key;
        }

        if (isset($map[$name]))
        {
            return array_search($name, $array);
        }

        return false;
    }

    /**
        Returns the first element of array
      
        @author Marcello Costa
    
        @package KeyClass\Code

        @param   array  $array    Target array
     
        @return  mixed|null  First elemento of array or null
    */
    public static function firstArrayItem(array &$array) {
        // If array is empty
        if (count($array) === 0) {
            return null;
        }

        // Resetting the pointer of array
        reset($array);

        // Returning the first element of array
        return $array[key($array)];
    }
    
    /**
        Returns the address (pointer) of last element of array
      
        @author Marcello Costa

        @package KeyClass\Code

        @param   array  $array Target array
     
        @return  mixed  Pointer to the last element of array
    */
    public static function lastArrayItem(array &$array) {
        // If array is empty
        if (count($array) === 0) {
            return null;
        }

        // Sending the pointer of array to the end
        end($array);

        // Returning the last element of array
        return $array[key($array)];
    }

    /**
        Functions that makes a merge between array overwritting elements
        with same key name
     
        Example:
         array_merge_recursive_distinct(
           array('key' => 'old value'),
           array('key' => 'new value')
         );
     
        Result:
         array('key' => array('new value'));
     
        The array are processed for the function as reference only
        for improve the performance. They are not changed by the function.
     
        @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
        @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
        @see <http://php.net/manual/pt_BR/function.array-merge-recursive.php>
     
        @package KeyClass\Code

        @param  array  $array1 Array 1
        @param  array  $array2 Array 2 (this array will overwrite the values of $array1)
     
        @return  array  Result of merge
    */
    public static function arrayMergeRecursiveDistinct(array &$array1, array &$array2) : array {
        $merged = $array1;

        foreach ($array2 as $key => &$value)
        {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
            {
                $merged[$key]=\KeyClass\Code::arrayMergeRecursiveDistinct($merged[$key], $value);
            }

            else
            {
                $merged[$key]=$value;
            }
        }

        return $merged;
    }

    /**
        Function that can be used for debug the code. The result is similar to
        "var_dump" function.

        @author Marcello Costa
     
        @package KeyClass\Code
     
        @param  mixed  $var    Variable to be displayed
     
        @return  void  Without return
    */
    public static function printDump($var) : void {
        echo '<pre dir="ltr" class="xdebug-var-dump">';
        print_r($var)."</pre>";
    }

    /**
        Removes PHP comments of a line
      
        @author Marcello Costa
      
        @package KeyClass\Code
     
        @param  string  $newline       Line to be processed
        @param  string  $commentfound  Flag for control if an comment has been found
     
        @return Void  Without return
    */
    public static function removePHPComments(string &$newline, string &$commentfound) : void {
        // If a comment as already been found
        if ($commentfound == true) {
            // While a closing tag was not been found
            // the line will be empty
            if (strpos($newline,'*/') === false) {
                $newline=null;
            }

            // If founds a final commment
            else {
                // It is necessary check where the comment is.
                // If is located on the end of line
                if (strpos((trim($newline)),'*/') == strlen(trim($newline))) {
                    // Então a linha fica em branco
                    $newline=null;
                }

                // If is not located on the end of line,
                // so we need to split what is comment and what is not
                else {
                   // From the position that starts to the end of line,
                   // removing everything
                   $cposStart=0;
                   $cposEnd=strpos($newline,'*/');
                   $replaceStr=\KeyClass\Code::extractString($newline, $cposStart, $cposEnd+2);
                   $newline=str_replace($replaceStr, "" , $newline);

                   if (trim($newline) == "*/") {
                       $newline=null;
                   }

                   $commentfound=false;
                }
            }
        }

        // If a comment still not founded
        else {
            // If a comments has been founded in just one line
            if (strpos($newline,'//') !== false) {
                // From the position that starts to the end, removes everything
                $cposStart=strpos($newline,'/*');
                $cposEnd=strlen($newline);
                $replaceStr=\KeyClass\Code::extractString($newline, $cposStart, $cposEnd);
                $newline=str_replace($replaceStr, "" , $newline);
            }

            else {
                // If a comment it's not founded in just one line, makes the normal
                // logic
                // Verifing if a comment exists
                if (strpos($newline,'/*') !== false) {
                    $commentfound=true;

                    // If a comment starts on the begining of line
                    // and if exists an end to him on the end of line
                    if ((strpos((trim($newline)),'/*') == 0) && (strpos((trim($newline)),'*}') === strlen(trim($newline)))) {
                        // So the line will be null
                        $newline=null;
                    }

                    else {
                        // If the end of comment not exists yet
                        if (strpos($newline,'*/') === false) {
                            $commentfound=true;
                            // From the position that starts to the end of line,
                            // removing everything
                            $cposStart=strpos($newline,'/*');
                            $cposEnd=strlen($newline);
                            $replaceStr=\KeyClass\Code::extractString($newline, $cposStart, $cposEnd);
                            $newline=str_replace($replaceStr, "" , $newline);
                        }

                        // If the end of comment is not the last thing writed on the line
                        else if (strpos((trim($newline)),'*/') !== strlen(trim($newline))) {
                            // From the position that starts to the end of line,
                            // removing everything
                            $cposStart=strpos($newline,'/*');
                            $cposEnd=strpos($newline,'*/');
                            $replaceStr=\KeyClass\Code::extractString($newline, $cposStart, $cposEnd+2);
                            $newline=str_replace($replaceStr, "" , $newline);
                            $commentfound=false;
                        }

                        // If comment don't start on the begining of line
                        else {
                            // If the comment ends on this line
                            if (strpos($newline,'*/') !== false) {
                                // Removes the comment
                                $cposStart=strpos($newline,'/*');
                                $cposEnd=strpos($newline,'*/');
                                $replaceStr=\KeyClass\Code::extractString($newline, $cposStart, $cposEnd+2);
                                $newline=str_replace($replaceStr, "" , $newline);
                                $commentfound=false;
                            }

                            // If the comment does not end on this line
                            else {
                                // From the position that starts to the end of line,
                                // removing everything
                                $cposStart=strpos($newline,'/*');
                                $cposEnd=strlen($newline);
                                $replaceStr=\KeyClass\Code::extractString($newline, $cposStart, $cposEnd);
                                $newline=str_replace($replaceStr, "" , $newline);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
        Returns the namespace, the classes and the methods inside an php file
      
        @author Marcello Costa
      
        @package KeyClass\Code
     
        @param  string  $filepath    Path of PHP file
     
        @return  array  Namespace and classes inside php file
    */
    public static function fileGetPHPTokens(string $filepath) : array {
        $phpCode = \KeyClass\FileTree::fileReadContent($filepath);
        $namespace="";
        $classes=array();
        $methods=array();
        \KeyClass\Code::getTokens($phpCode, $namespace, $classes, $methods);

        // If the namespace has been not found, throws an error
        if ($namespace === "") {
            throw new \Exception("No namespace was found in the file <b>".$filepath."</b> !");
        }

        // Returns the founded values
        return (array('namespace' => $namespace, 'classes' => $classes, 'methods' => $methods));
    }

    /**
        Returns the namespace and clases inside an php code
     
        @author Marcello Costa
      
        @package KeyClass\Code
     
        @param  string  $phpCode      PHP code that contains classes
        @param  string  $namespace    External variable which will be receive the name of namespace of the code
        @param  array   $classes      External variable which will be receive the names of classes of code
        @param  array   $methods      External variable which will be receive the names of methods of code
     
        @return  void  Without return
    */
    public static function getTokens(string $phpCode, string &$namespace, array &$classes, array &$methods) : void {
        // Yes, I know: the functions "token_get_all" can be used without this
        // complicated logic below. But, to avoid problems, the comments of php
        // code are removed before.

        // Require KeyClass to manage comments
        \KeyClass\FileTree::requireOnceFile('frame_src/keyclasses/php/code.php');

        // Flag for comments found
        $commentfound=false;

        // Code without comments
        $nocomments_code=array();
        $phpCode=explode("\n", $phpCode);

        foreach($phpCode as $line_num => $line) {
            // Variable to the new line formatted
            $newline=$line;

            // Removing comments
            \KeyClass\Code::removePHPComments($newline,$commentfound);

            // Insert the code without comments on array
            if ($newline != null) {
                $nocomments_code[]=$newline;
            }
        }

        unset($phpCode);
        $codestring="";
        foreach($nocomments_code as $l) {
            $codestring.="\r\n".$l;
        }

        // Turn everything in token
        $tokens=token_get_all($codestring);

        // Getting the namespace
        $namespace=\KeyClass\Code::getNamespace($tokens);

        // Getting the functions
        $methods=\KeyClass\Code::getFunctions($tokens);

        // Getting the classes
        $classes=\KeyClass\Code::getClasses($tokens);
    }

    /**
        Returns the namespace inside an array of tokens
     
        @author Marcello Costa
      
        @package KeyClass\Code
     
        @param  array  $tokens    Tokens of a php code
     
        @return  string  Name of namespace
    */
    public static function getNamespace(array $tokens) : string {
        $count = count($tokens);
        $nfound=false;
        $namespace="";

        for ($i = 2; $i < $count; $i++) {
          if ($tokens[$i][0] == T_NAMESPACE) {
              $nfound=$i;
          }

          if ($nfound !== false) {
              $i2=$i;
              while ($tokens[$i2] !== ";") {
                $namespace .= $tokens[$i2][1];

                $i2++;
                // Loop break
                if ($i2 === 1000) {
                    return false;
                }
              }
              $nfound=false;

              $namespace=str_replace("namespace ", "", $namespace);

              return($namespace);
          }
        }
    }

    /**
        Returns the classes inside an array of tokens
     
        @author Marcello Costa
      
        @package KeyClass\Code
     
        @param  array  $tokens    Tokens of a php code
     
        @return  array  Founded classes in tokens
    */
    public static function getClasses(array $tokens) : array {
        $classes = array();
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
          if (   $tokens[$i - 2][0] == T_CLASS
              && $tokens[$i - 1][0] == T_WHITESPACE
              && $tokens[$i][0] == T_STRING) {

              $className = $tokens[$i][1];
              $classes[] = $className;
          }
        }
        return $classes;
    }

    /**
        Returns the functions inside an array of tokens
     
        @author Marcello Costa
      
        @package KeyClass\Code
     
        @param  array  $tokens    Tokens of a php code
     
        @return  array  Founded functions in tokens
    */
    public static function getFunctions(array $tokens) : array {
        $count = count($tokens);
        $methods=array();

        for ($i = 2; $i < $count; $i++) {
          if ($tokens[$i][0] == T_FUNCTION) {
              $methods[]=$tokens[$i+2][1];
          }
        }

        return $methods;
    }

    /**
        Gets a piece of an string by index (start/end)
     
        @author Marcello Costa
     
        @package KeyClass\Code
     
        @param  string  $string       Complete string
        @param  int     $IndexStart   Index of start
        @param  int     $IndexEnd     Index of end
     
        @return  string  Returns the piece of string
    */
    public static function extractString(string $string, int $IndexStart, int $IndexEnd) : string {
      return substr($string, $IndexStart, $IndexEnd-$IndexStart);
    }

    /**
        Converts special characters to regular characters
      
        @author Marcello Costa
     
        @package KeyClass\Code

        @param  string  $string                String to be converted
        @param  bool    $convertwhitespaces    Flag to convert or not white spaces
     
        @return  String  String converted
    */
    public static function specialCharsToAlphaNumeric(string $string, bool $convertwhitespaces=false) : string {
        // Keeping the white spaces
        if ($convertwhitespaces === false) {
            $transliterationTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'E', 'ё' => 'e', 'Ё' => 'E', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
        }

        // Removing white spaces
        else {
            $transliterationTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'E', 'ё' => 'e', 'Ё' => 'E', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja', ' ' => '');
        }

        // Returning converted string
        return str_replace(array_keys($transliterationTable), array_values($transliterationTable), $string);
    }

    /**
        Converts an object to array
     
        @author Marcello Costa
     
        @package KeyClass\Code
     
        @param  mixed  $object    Object/array to be converted
     
        @return  array  Object converted to array
    */
    public static function objectToArray($object) : array {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        }
        else {
            return array_map(array($this, 'objectToArray'), (array) $object);
        }
    }
}
