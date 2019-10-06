<?php
/**
  Arquivo DatabaseConnectors\Mysql_Connector
*/

// Namespace de conectores
namespace Modules\DatabaseConnectors;

/**
  Conector para DBMS MySQL
  
  @author Marcello Costa

  @package DatabaseConnectors
*/
class Mysql_Connector{
  /**
      Função de construção do objeto
   
      @author Marcello Costa

      @package DatabaseConnectors\Mysql_Connector
   
      @param  string  $connection   Nome do pack do controller
   
      @return bool Retorno da operação
  */
  public static function connect(&$connection) : bool {
    // Se o DMBS está errado
    if (property_exists($connection, "dbms") && strtolower(trim($connection->dbms)) !== "mysql") {
      primaryError("Incorrect DBMS option for connector");
    }

    // Colocando o charset correto
    if ($connection->persistent === false) {
        $connection->conexao = new \PDO(
            "mysql:host=".$connection->hostname.";dbname=".$connection->databasename.""."; port=".$connection->port."; charset=".$connection->charset, $connection->username, $connection->password,array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );

        $connection->conexao->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    // Conexão persistente
    else {
        $connection->conexao = new \PDO(
            "mysql:host=".$connection->hostname.";dbname=".$connection->databasename."; port=".$connection->port."; charset=".$connection->charset, $connection->username, $connection->password,array(\PDO::ATTR_PERSISTENT => true, \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );
    }

    // Nível do lock do mysql para as transações deste model (caso necessário)
    if (strtolower(trim($connection->isolationLevel)) != "default" &&
        strtolower(trim($connection->isolationLevel)) != "" &&
        strtolower(trim($connection->isolationLevel)) != null
       ) {
        $connection->conexao->query("SET SESSION TRANSACTION ISOLATION LEVEL ".$connection->isolationLevel);
    }
    
    // Ativando os erros
    $connection->conexao->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    
    return true;
  }
}
