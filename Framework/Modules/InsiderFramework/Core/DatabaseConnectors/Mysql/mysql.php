<?php

// Namespace de conectores
namespace Modules\DatabaseConnectors;

/**
 * DBMS MySQL Connector
 *
 * @author Marcello Costa <marcello88costa@yahoo.com.br>
 *
 * @package Modules\DatabaseConnectors
 */
class MysqlConnector
{
  /**
   * Função de construção do objeto
   *
   * @author Marcello Costa
   *
   * @package DatabaseConnectors\MysqlConnector
   *
   * @param Modules\InsiderFramework\Core\Model $model Model linked to connection
   *
   * @return bool Return of operation
   */
    public static function connect($model): bool
    {
        $typeOfModel = strtolower(strtok((new \ReflectionObject($model))->getNamespaceName(), "\\"));

      // Checks if the $model it's not a model
        if ($typeOfModel !== "models") {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister('');
        }

        if (property_exists($model, "dbms") && strtolower(trim($model->dbms)) !== "mysql") {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("Incorrect DBMS option for connector");
        }

        if ($model->persistent === false) {
            $model->connection = new \PDO(
                "mysql:host=" . $model->hostname . ";dbname=" .
                $model->databasename . "" . "; port=" . $model->port .
                "; charset=" . $model->charset,
                $model->username,
                $model->password,
                array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );

            $model->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } else {
            $model->connection = new \PDO(
                "mysql:host=" . $model->hostname . ";dbname=" .
                $model->databasename . "; port=" . $model->port .
                "; charset=" . $model->charset,
                $model->username,
                $model->password,
                array(\PDO::ATTR_PERSISTENT => true, \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
        }

        if (
            strtolower(trim($model->isolationLevel)) != "default" &&
            strtolower(trim($model->isolationLevel)) != "" &&
            strtolower(trim($model->isolationLevel)) != null
        ) {
            $model->connection->query(
                "SET SESSION TRANSACTION ISOLATION LEVEL " .
                $model->isolationLevel
            );
        }

        $model->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return true;
    }
}