<?php
/**
  Arquivo KeyClass\JSON
*/

// Namespace das KeyClass
namespace KeyClass;

/**
   KeyClass de tratamento de arquivos JSON

   @package KeyClass\JSON

   @author Marcello Costa
 */
class JSON{
    /**
        Recupera informações de um arquivo JSON
      
        @author Marcello Costa
     
        @package KeyClass\JSON
     
        @param  string  $filepath    Path of the JSON file 
        @param  bool    $assoc       Retorna um array associativo ao invés de objeto
     
        @return  array|bool  Informações do arquivo JSON se conseguir ler. Se não
                             conseguir ler, retorna false.
    */
    public static function getJSONDataFile(string $filepath, bool $assoc = true) {
        if (file_exists($filepath)) {
            // Recuperando as linhas do arquivo
            $filecontent=\KeyClass\FileTree::fileReadContent($filepath);

            $t=json_decode($filecontent);
            
            if ($t === null){
                return false;
            }

            // Retornando as informações
            return(json_decode($filecontent, $assoc));
        }
        else {
            return false;
        }
    }

    /**
        Grava informações em um arquivo JSON
     
        @author Marcello Costa
      
        @package KeyClass\JSON
     
        @param  mixed   $data       Dados a serem armazenados no arquivo JSON
        @param  string  $filepath   Path of the JSON file 
        @param  bool    $overwrite  Sobreescrever dados ou não
     
        @return  bool  Retorno de sucesso ou não da gravação do arquivo
    */
    public static function setJSONDataFile($data,  string $filepath, bool $overwrite=false) : bool {
        // Encodando os dados
        $datafile=\KeyClass\JSON::jsonEncodePrivateObject($data);

        // Gravando no arquivo
        $return=\KeyClass\FileTree::fileWriteContent($filepath, $datafile, $overwrite);
        
        if ($return === false) {
            primaryError("Unable to write to file: ".$filepath);
        }

        return true;
    }

    /**
        Função que verifica se um string é um JSON
     
        @author Marcello Costa
      
        @package KeyClass\JSON
     
        @param  string  $value    String a ser verificada
     
        @return  bool  Se for um JSON retorna true
    */
    public static function isJSON(string $value) : bool {
        // Tentando decodificar o JSON
        $r=json_decode($value);

        // Se for um JSON, retorna true
        if ($r !== null) {
            return true;
        }

        // Se não for um JSON, retorna false
        else {
            return false;
        }
    }
    
    
    /**
        Função que extrai as propriedades privadas de um objeto

        @author Marcello Costa
        @author Petah
        @author Andre Medeiros
        @see https://stackoverflow.com/questions/7005860/php-json-encode-class-private-members

        @package Core

        @param  Object  $object    Objeto a ter as propriedades extraídas

        @return  string  Objeto convertido em string
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
        Função que extrai as propriedades privadas de um objeto

        @author Marcello Costa
        @author Petah
        @author Andre Medeiros
        @see https://stackoverflow.com/questions/7005860/php-json-encode-class-private-members

        @package Core

        @param  Object  $object    Objeto a ter as propriedades extraídas

        @return  array|bool  Array que equivale ao objeto ou false se não for um objeto
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
