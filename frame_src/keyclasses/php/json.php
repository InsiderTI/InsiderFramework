<?php
/**
  KeyClass\JSON
*/

namespace KeyClass;

/**
   KeyClass to handle JSON files

   @package KeyClass\JSON

   @author Marcello Costa
 */
class JSON{
    /**
        Get the data of a JSON file
      
        @author Marcello Costa
     
        @package KeyClass\JSON
     
        @param  string  $filepath    Path of the JSON file 
        @param  bool    $assoc       If this is true the function will return 
                                     an associative array instead of an object
     
        @return  array|bool  Data of JSON file if the file can be read. If not, returns false
    */
    public static function getJSONDataFile(string $filepath, bool $assoc = true) {
        if (file_exists($filepath)) {
            // Getting the content of the file
            $filecontent=\KeyClass\FileTree::fileReadContent($filepath);

            $t=json_decode($filecontent);
            
            if ($t === null){
                return false;
            }

            // Retuning the data
            return(json_decode($filecontent, $assoc));
        }
        else {
            return false;
        }
    }

    /**
        Records data to a JSON file
     
        @author Marcello Costa
      
        @package KeyClass\JSON
     
        @param  mixed   $data       Data to be recorded
        @param  string  $filepath   Path of the JSON file 
        @param  bool    $overwrite  If this is true, overwrites the data of JSON file
     
        @return  bool  Processing result
    */
    public static function setJSONDataFile($data,  string $filepath, bool $overwrite=false) : bool {
        // Encoding the data
        $datafile=\KeyClass\JSON::jsonEncodePrivateObject($data);

        // Recording the content in the file
        $return=\KeyClass\FileTree::fileWriteContent($filepath, $datafile, $overwrite);
        
        if ($return === false) {
            primaryError("Unable to write to file: ".$filepath);
        }

        return true;
    }

    /**
        Checks if a string is a JSON 
     
        @author Marcello Costa
      
        @package KeyClass\JSON
     
        @param  string  $value    String to be verified
     
        @return  bool  If the string is an JSON, return true
    */
    public static function isJSON(string $value) : bool {
        // Trying decode the JSON
        $r=json_decode($value);

        // If it is a JSON, returns true
        if ($r !== null) {
            return true;
        }

        // If it is not a JSON, return false
        else {
            return false;
        }
    }
    
    
    /**
        Function that extract the private properties of an object and
        return this properties as a JSON string

        @author Marcello Costa
        @author Petah
        @author Andre Medeiros
        @see https://stackoverflow.com/questions/7005860/php-json-encode-class-private-members

        @package Core

        @param  Object  $object    Object that will be readed/extracted

        @return  string  String that represents the object
    */
    public static function jsonEncodePrivateObject($object) : string {
        if (is_object($object)){
            return json_encode(\KeyClass\JSON::extractObjectPrivateProps($object));
        }
        else{
            return json_encode($object);
        }
    }

    /**
        Function that extract the private properties of an object

        @author Marcello Costa
        @author Petah
        @author Andre Medeiros
        @see https://stackoverflow.com/questions/7005860/php-json-encode-class-private-members

        @package Core

        @param  Object  $object    Object that will be readed/extracted

        @return  array|bool  Array that represents the object or false (if it is not an object)
    */
    public static function extractObjectPrivateProps($object) : array {
        $public = [];
        
        if (!is_object($object)){
            return false;
        }

        $reflection = new \ReflectionClass(get_class($object));

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            $value = $property->getValue($object);
            $name = $property->getName();

            if (is_array($value)) {
                $public[$name] = [];

                foreach ($value as $item) {
                    if (is_object($item)) {
                        $itemArray = \KeyClass\JSON::extractObjectPrivateProps($item);
                        $public[$name][] = $itemArray;
                    } else {
                        $public[$name][] = $item;
                    }
                }
            } else if (is_object($value)) {
                $public[$name] = \KeyClass\JSON::extractObjectPrivateProps($value);
            } else
                $public[$name] = $value;
        }

        return $public;
    }
}
