<?php
/**
  Arquivo KeyClass\XML
*/

// Namespace das KeyClass
namespace KeyClass;

/**
   KeyClass de tratamento de arquivos XML

   @package KeyClass\XML
   
   @author Marcello Costa
*/
class XML{
    /**
        Converte um objeto em um array

        @author Marcello Costa

        @package KeyClass\XML
     
        @param  Array         $data            Array a ser convertido
        @param  bool|string   $xmlData        Objeto de dados do XML
        @param  bool|string   $fixNumericKeys  Se diferente de false, seta um
                                               prefixo para chaves numéricas no
                                               XML
     
        @return  SimpleXMLObject  Objeto SimpleXML
     */
    public static function arrayToXML(Array $data, &$xmlData, $fixNumericKeys=false) : SimpleXMLObject {
        $keys=array_keys($data);

        $root = "root";

        if (!is_object($xmlData)) {
            $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><'.$root.'></'.$root.'>');
        }

        if (count($keys) === 1) {
            $loop[$keys[0]]=$data[$keys[0]];
        }
        else {
            $loop=$data;
        }

        foreach($loop as $key => $value) {
            if (is_numeric($key)) {
                if ($fixNumericKeys !== false && $fixNumericKeys !== null) {
                    $key = (string)$fixNumericKeys.$key;
                }
                else {
                    \KeyClass\Error::i10nErrorRegister("Error converting Array to XML: numeric keys were encountered", 'pack/sys');
                }
            }
            if (is_array($value)) {
                $subnode = $xmlData->addChild($key);
                \KeyClass\XML::arrayToXML($value, $subnode, $fixNumericKeys);
            }
            else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }

        return $xmlData;
    }

    /**
        Função que verifica se um string é um XML
     
        @author Marcello Costa

        @package KeyClass\XML
     
        @param  string  $xmlstr    String a ser verificada
     
        @return  bool  Se for um XML retorna true
    */
    public static function isXML(string $xmlstr) : bool {
        libxml_use_internal_errors(true);
        simplexml_load_string($xmlstr);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if (count($errors) === 0) {
            return true;
        }
        else {
            return false;
        }
    }
}
