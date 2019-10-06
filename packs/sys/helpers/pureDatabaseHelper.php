<?php
/**
  Helper com funções para manipulação de bancos de dados
*/
namespace Helpers;

/**
  Classe Helper contendo funções para manipulação de bancos de dados
  
  @author Marcello Costa
  @package Helpers\pureDatabaseHelper
*/
class pureDatabaseHelper{
    /**
        Cria filtros para as buscas (select) com pureDatabase
     
        @author Marcello Costa
        @package Helpers\pureDatabaseHelper

        @param  array  $searchData    Array de dados sendo buscados
        @param  string $filter        String que será o filtro
      
        @return void  A função modifica diretamente os argumentos recebidos
    */
    static function makeFiltersSelect(array &$searchData, string &$filter) {
        if (is_array($searchData)) {
            $filter = implode(' and ',
                array_map(
                    function($item) { return $item." = :".$item; },
                    array_keys($searchData))
            );
            
            $filter = " where ".$filter;
        }
    }

    /**
        Busca um único resultado. Se encontrar múltiplo, retorna false. Se
        não encontrar nenhum, retorna null
     
        @author Marcello Costa
        @package Helpers\pureDatabaseHelper
     
        @param  object  $model      Model que está requisitando a função
        @param  string  $select     Select contendo a busca (sem o where)
        @param  array   $searchData Array de dados sendo buscados
      
        @return array  Array com o resultado único
    */
    static function getOneOrNone($model, string $select, array $searchData) {
        $filter="";
        pureDatabaseHelper::makeFiltersSelect($searchData, $filter);
        
        $query=$select." ".$filter;

        // Retornando dados
        $resultquery=$model->select($query, $searchData, false);

        // Se não encontrou registros
        if (empty($resultquery)) {
            return null;
        }
        if (count($resultquery) > 1) {
            return false;
        }
        return $resultquery[0];
    }

    /**
        Busca um único resultado por um id
     
        @author Marcello Costa
        @package Helpers\pureDatabaseHelper
     
        @param  object  $model      Model que está requisitando a função
        @param  string  $table      Nome da table que está sendo buscada
        @param  int     $id         ID do registro que está sendo buscado
      
        @return array  Array com o resultado único
    */
    static function find($model, string $table, int $id) {
        $pkQuery=$model->select("SHOW COLUMNS FROM ".$table, true);
        
        $pkColumn = null;
        foreach ($pkQuery as $column) {
            if ($column['Key'] === 'PRI') {
                $pkColumn = $column['Field'];
            }
        }
        if ($pkColumn === null) {
            return null;
        }
        
        $query = "select * from `".$table."` where ".$pkColumn." = :id";
        $resultquery=$model->select($query, array('id' => $id), false);
        
        return $resultquery;
    }
    
    /**
        Busca rows por um array de dados específico.
     
        @author Marcello Costa
        @package Helpers\pureDatabaseHelper
     
        @param  object  $model      Model que está requisitando a função
        @param  string  $table      Nome da table que está sendo buscada
        @param  array   $searchData Array de dados sendo buscados
      
        @return array  Array com o resultado único
    */
    static function findBy($model, string $table, array $searchData) {
        $filter="";
        pureDatabaseHelper::makeFiltersSelect($searchData, $filter);
        
        $query="select * from ".$table." ".$filter;

        // Buscando dados
        $resultquery=$model->select($query, $searchData, false);

        // Retornando dados
        return $resultquery;
    }
    
    /**
        Atualiza um ou mais registros de acordo com as condições especificadas
     
        @author Marcello Costa
        @package Helpers\pureDatabaseHelper
     
        @param  object  $model      Model que está requisitando a função
        @param  string  $table      Nome da table que está sendo buscada
        @param  array   $searchData Array de dados sendo buscados ou string
        @param  array   $newData    Array de novos dados
      
        @return integer Número de rows afetadas na operação
      
    */
    static function updateBy($model, string $table, array $searchData, array $newData) {
        if (trim($table) === "") {
            \KeyClass\Error::i10nErrorRegister('Table name cannot by empty on update', 'pack/sys');
        }
        
        if (count($newData) === 0) {
            \KeyClass\Error::i10nErrorRegister('New data not specified on updateBy with table %'.$table.'%', 'pack/sys');
        }

        $updateColumns = implode(', ',
            array_map(
                function($item) { return $item." = :".$item; },
                array_keys($newData))
        );
        
        $filter="";

        if (count($searchData) === 0) {
            \KeyClass\Error::i10nErrorRegister('Search data not specified on updateBy with table %'.$table.'%', 'pack/sys');
        }

        pureDatabaseHelper::makeFiltersSelect($searchData, $filter);            
        
        $query = "UPDATE `".$table."` SET "
                        . $updateColumns
                        . $filter
                        . ";";
        
        $bindArray = array_merge($searchData, $newData);

        // Realizando update
        $result=$model->execute($query, $bindArray);

        // Erro
        if (!is_numeric($result)) {
            \KeyClass\Error::errorRegister('Error on execute update query %'.$query.'%: %'.\KeyClass\JSON::jsonEncodePrivateObject($result).'%', __FILE__, __LINE__);
        }
        
        return $result;
    }
    
    /**
        Cria um ou mais registros de acordo com as condições especificadas
     
        @author Marcello Costa
        @package Helpers\pureDatabaseHelper
     
        @param  object  $model      Model que está requisitando a função
        @param  string  $table      Nome da table que está sendo buscada
        @param  array   $newData    Array de novos dados
      
        @return integer Número de rows afetadas na operação
      
    */
    static function insert($model, string $table, array $newData) {
        if (trim($table) === "") {
            \KeyClass\Error::i10nErrorRegister('Table name cannot by empty on insert', 'pack/sys');
        }
        
        if (count($newData) === 0) {
            \KeyClass\Error::i10nErrorRegister('New data not specified on insert with table %'.$table.'%', 'pack/sys');
        }

        $insertColumns = implode(', ',
            array_map(
                function($item) { return $item." = :".$item; },
                array_keys($newData))
        );

        $query = "INSERT INTO `".$table."` SET "
                        . $insertColumns
                        . ";";
        
        $bindArray = $newData;

        // Realizando update
        $result=$model->execute($query, $bindArray);

        // Erro
        if (!is_numeric($result)) {
            \KeyClass\Error::errorRegister('Error on execute insert query %'.$query.'%: %'.\KeyClass\JSON::jsonEncodePrivateObject($result).'%', __FILE__, __LINE__);
        }
        
        return $result;
    }
}

