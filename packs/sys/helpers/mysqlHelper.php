<?php
/**
  Helper com funções globai
*/
namespace Helpers;

/**
  Classe Helper contendo funções de auxílio quando utilizar mysql
  
  @author Marcello Costa
  @package Helpers\globalHelper
*/
class mysqlHelper{
    /**
        Converte uma string datetime (mysql) para um array contendo a data e
        hora separadamente (e em formato pt-br por padrão)
      
        @author Marcello Costa
     
        @package KeyClass\Code
     
        @param  string  $datetime    String a ser convertida
     
        @return  array  Data e hora separadamente
    */
    public function convertTimeStampMysqlToArray(string $datetime, $format = 'pt-br') {
        $timestamp_tmp=  explode("-", $datetime);

        $ano=$timestamp_tmp[0];
        $mes=$timestamp_tmp[1];
        $diahora=$timestamp_tmp[2];

        $dia_tmp=explode(" ", $diahora);
        $dia=$dia_tmp[0];
        $hora=$dia_tmp[1];

        if (intval($dia) < 10) {
            $dia="0".intval($dia);
        }
        if (intval($mes) < 10) {
            $mes="0".intval($mes);
        }

        switch(strtolower($format)) {
            case 'pt-br':
                // Retornando conversão
                return array(
                  'date' => $dia."/".$mes."/".$ano,
                  'time' => $hora
                );
            break;
            default:
                primaryError("Format to convert timestampmysql to array not implemented yet: ".$format);
            break;
        }
    }
}