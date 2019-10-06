<?php
/**
  Helper com funções globai
*/
namespace Helpers;

/**
  Classe Helper contendo funções globais que podem ser utilizadas facilmente em
  todo o framework.
  
  @author Marcello Costa
  @package Helpers\globalHelper
*/
class globalHelper{
    /**
        Verifica se uma posição no array existe e está preenchida com
        um array não vazio, número, string não vazia, objeto ou resource
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @param  Array  $array    Array onde está sendo buscado
        @param  String  $key     Chave que será utilizada para verificação
     
        @return  Bool  Se o elemento existe e não é vazio
    */
    public static function existAndIsNotEmpty(array $array, string $key) {
        if (array_key_exists($key, $array)){
            if (
                (
                    (is_array($array[$key]) && !empty($array[$key])) ||
                    ((is_string($array[$key]) || is_numeric($array[$key])) && $array[$key] !== "") ||
                    (is_resource($array))
                )
               )
            {
                return true;
            }
        }
        return false;
    }

    /**
        Verifica se uma posição no array existe e é um email válido
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @param  Array  $array    Array onde está sendo buscado
        @param  String  $key     Chave que será utilizada para verificação
     
        @return  Bool  Se o elemento existe e é um email válido
    */
    public static function existAndIsEmail(array $array, string $key) {
        if (isset($array[$key]) && $array[$key]."" !== "") {
            return \KeyClass\Validate::CheckEmail($array[$key]);
        }
        return false;
    }

    /**
        Verifica se uma posição no array existe e está preenchida com um número
        positivo
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @param  Array  $array    Array onde está sendo buscado
        @param  String  $key     Chave que será utilizada para verificação
     
        @return  Bool  Se o elemento existe e é positivo (numeral)
    */
    public static function existAndIsPositive(array $array, string $key) {
        if (isset($array[$key]) && $array[$key]."" !== "") {
            if (floatval($array[$key]) > 0) {
                return true;
            }
            else {
                return false;
            }
        }
        return false;
    }

    /**
        Verifica se uma posição no array existe e está preenchida com um número
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @param  Array  $array    Array onde está sendo buscado
        @param  String  $key     Chave que será utilizada para verificação
     
        @return  Bool  Se o elemento existe e é numérico
    */
    public static function existAndIsNumeric(array $array, string $key) {
        if (isset($array[$key]) && $array[$key]."" !== "") {
            if (is_numeric($array[$key])) {
                return true;
            }
            else {
                return false;
            }
        }
        return false;
    }

    /**
        Verifica se uma posição no array existe e está preenchida com um valor
        em reais válido
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @param  Array  $array    Array onde está sendo buscado
        @param  String  $key     Chave que será utilizada para verificação
     
        @return  Bool  Se o elemento existe e é inteiro
    */
    public static function existAndIsMoney(array $array, string $key) {
        if (isset($array[$key]) && $array[$key]."" !== "") {
            $value=getMoneyArray($array[$key]);
            if (trim($value['reais']) !== "" && trim($value['centavos']) !== "") {
                return true;
            }
        }
        return false;
    }

    /**
        Extrai o valor em reais e os centavos de uma string, separando em um array
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @param  String  $value    Valor a ser analisado
        @param  String  $format   Formato do dinheiro
     
        @return  Array  Array que contém 'reais' e 'centavos' como chaves
    */
    public static function getMoneyArray(string $value, string $format=LINGUAS) {
        switch (strtolower($format)) {
            case 'pt_br':
                $regex = "/(?P<reais>^[0-9]{1,3}([.]([0-9]{3}))*)[,](?P<centavos>([.]{0})[0-9]{0,2}$)/";
                preg_match($regex, $value, $matches);
                if (count($matches) > 0) {
                    if (trim($matches['reais']) !== "" && trim($matches['centavos']) !== "") {
                        return array(
                            'reais' => $matches['reais'],
                            'centavos' => $matches['centavos']
                        );
                    }
                }

                return false;
            break;
            default:
                \KeyClass\Error::errorRegister('Not implemented', "CRITICAL");
            break;
        }
    }

    /**
        Verifica se um campo é uma data válida
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @param  Array  $array      Array onde está sendo buscado
        @param  String  $key       Chave que será utilizada para verificação
        @param  String  $format    Formato da data sendo verificada
     
        @return  Bool  Se é uma data válida, retorna true
    */
    public static function existAndIsDate(array $array, string $key, string $format='d/m/Y') {
        if (isset($array[$key])) {
            $date = $array[$key];

            return IsDate($date, $format);
        }
        return false;
    }

    /**
        Verifica se uma variável é uma data
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @param  String  $date      String a ser verificada
        @param  String  $format    Formato da data sendo verificada
     
        @return  Bool  Se é uma data válida, retorna true
    */
    public static function IsDate(string $date, string $format='Y-m-d') {
        $d = \DateTime::createFromFormat($format, $date);
        if ($d && $d->format($format) == $date) {
            return true;
        }
        return false;
    }

    /**
        Verifica se um campo é uma data válida
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @param  Array  $array    Array onde está sendo buscado
        @param  String  $key     Chave que será utilizada para verificação
     
        @return  Bool  Se é uma data válida, retorna true
    */
    public static function existAndIsDateTime(array $array, string $key) {
        if (isset($array[$key])) {
            $datetime = str_replace('/', '-', $array[$key]);
            return \DateTime('Y-m-d H:i:s', strtotime($datetime));
        }
        return false;
    }

    /**
        Verifica se é uma requisição ajax
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @return  Bool  Se é uma requisição ajax ou não
    */
    public static function isAjaxRequest() : bool {
        global $kernelspace;
        $ajaxrequest = $kernelspace->getVariable('ajaxrequest', 'insiderRoutingSystem');
        return $ajaxrequest;
    }
    
    /**
        Verifica se o responseFormat é o esperado
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @return  Bool  Se true, está correto
    */
    public static function isResponseFormat($responseFormatExpected){
        global $kernelspace;
        $responseFormatNow = $kernelspace->getVariable('responseFormat', 'insiderFrameworkSystem');
        
        if (strtoupper($responseFormatExpected) === strtoupper($responseFormatNow)){
            return true;
        }
        
        return false;
    }
    
    /**
        Função para ler um token JWT
     
        @author Marcello Costa
        @package Helpers\globalHelper
     
        @return  array  Array de dados do token
    */
    public static function readBearer() : array {
        $token = null;
        $headers = apache_request_headers();
        $data = [];

        if (isset($headers['Authorization'])) {
          $matches = array();
          preg_match('/Bearer (.*)/', $headers['Authorization'], $matches);
          if (isset($matches[1])) {
            $token = $matches[1];
          }
          
        }
        else {
            $data['error']="No Bearer found";
        }
        
        return $data;
    }
}
?>
