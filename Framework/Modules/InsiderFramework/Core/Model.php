<?php

namespace Modules\InsiderFramework\Core;

/**
 * Class responsible for communicating with the database
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Model
 */
class Model
{
    use \Modules\InsiderFramework\PureSql\Core;
    
    /**
     * Variables for database connection
     *
     * @var string $hostname       Hostname
     * @var string $connector      Connector Name Used
     * @var string $databasename   Database Name
     * @var string $username       Database Connection User
     * @var string $password       Database connection password
     * @var bool   $persistent     Persistent or not connection
     * @var string $isolationLevel Database Isolation Level
     * @var string $charset        Charset to be used
     * @var int    $port           Database connection port
     * @var string $dbms           Database Management System
     */
    public $hostname;
    public $connector;
    public $databasename;
    public $username;
    public $password;
    public $persistent;
    public $isolationLevel;
    public $charset;
    public $port;
    public $dbms;

    /** @var bool $connection Database connection status variable */
    public $connection = false;

    /**
     * Constructor of the class that receives the declared parameters
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Model
     *
     * @param string $database Database name
     *
     * @return void
     */
    public function __construct(string $database = null)
    {
        $databases = \Modules\InsiderFramework\Core\KernelSpace::getVariable('databases', 'insiderFrameworkSystem');

        if ($database == null) {
            if (defined('BD_APP')) {
                $databasechoice = $databases[BD_APP];
                $this->databasename = $databases[BD_APP]['databasename'];
            } else {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister("Default database not defined in global database array", "app/sys");
            }
        } else {
            if (!isset($databases[$database])) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Database not found in global database array: %" . $database . "%",
                    "app/sys"
                );
            }
            $databasechoice = $databases[$database];
            $this->databasename = $databases[$database]['databasename'];
        }

        $this->hostname = $databasechoice["hostname"];
        $this->connector = $databasechoice["connector"];

        $this->username = $databasechoice["username"];
        $this->password = $databasechoice["password"];

        $this->port = $databasechoice["port"];

        if (isset($databasechoice["dbms"])) {
            $this->dbms = $databasechoice["dbms"];
        }

        $this->charset = $databasechoice["charset"];

        $this->isolationLevel = $databasechoice["isolationLevel"];

        if ($databasechoice["persistent"]) {
            $this->persistent = true;
            $this->connect();
        } else {
            $this->persistent = false;
        }

        if (method_exists($this, 'customConstruct')) {
            call_user_func_array(array($this, 'customConstruct'), array(serialize($this)));
        }
    }

    /**
     * Function to check if the model is currently connected to the database
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Model
     *
     * @return bool Return of verification
     */
    private function checkConnection(): bool
    {
        if (isset($this->connection) && $this->connection !== false) {
            return true;
        }

        return false;
    }

    /**
     * Role connecting to the database
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Model
     *
     * @return bool Returns true only to say that you are already logged in
     */
    private function connect(): bool
    {
        if ($this->checkConnection()) {
            return true;
        }

        $arrayError['hostname'] = $this->hostname;
        $arrayError['connector'] = $this->connector;
        $arrayError['databasename'] = $this->databasename;
        $arrayError['userName'] = $this->username;
        $arrayError['password'] = $this->password;
        $arrayError['persistent'] = $this->persistent;
        $arrayError['dbms'] = $this->dbms;

        if (
            $this->hostname === null ||
            $this->connector === null ||
            $this->databasename === null ||
            $this->username === null ||
            $this->password === null ||
            $this->persistent === null
        ) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Incomplete parameters of connection with database: %" . json_encode($arrayError) . "%",
                "app/sys"
            );
        }

        $connectorPath = INSTALL_DIR . DIRECTORY_SEPARATOR .
        "Framework" . DIRECTORY_SEPARATOR .
        "Modules" . DIRECTORY_SEPARATOR .
        "InsiderFramework" . DIRECTORY_SEPARATOR .
        "Core" . DIRECTORY_SEPARATOR .
        "DatabaseConnectors" . DIRECTORY_SEPARATOR .
        ucfirst(strtolower($this->connector)) .
        DIRECTORY_SEPARATOR . strtolower($this->connector) . ".php";

        if (!file_exists($connectorPath)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Database file connector not found: " .
                "%" . ucfirst(strtolower($this->connector)) . DIRECTORY_SEPARATOR . strtolower($this->connector) . "%",
                "app/sys"
            );
        }

        \Modules\InsiderFramework\Core\FileTree::requireOnceFile($connectorPath);

        $databaseClass = "Modules\DatabaseConnectors\\" . ucfirst(strtolower(trim($this->connector)));

        try {
            if (!class_exists($databaseClass . "Connector")) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Database connector not detected: %" . $databaseClass . "Connector%",
                    "app/sys"
                );
            }

            $databaseClass = "\\" . $databaseClass . "Connector";
            $connector = new $databaseClass();

            return $connector->connect($this);
        } catch (\PDOException $e) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Database connection error: %" . $e->getMessage() . "%",
                "app/sys"
            );
        }
    }

    /**
     * Function that terminates the bank connection
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Model
     *
     * @return void
     */
    private function disconnect(): void
    {
        if ($this->persistent === false) {
            if ($this->checkConnection()) {
                try {
                    unset($this->connection);
                } catch (\PDOException $e) {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        "Database disconnection error: %" . $e->getMessage() . "%",
                        "app/sys"
                    );
                }
            }
        }
    }

    /**
     * Function that performs a "select" in the database
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Model
     *
     * @param string $query             Query containing Select
     * @param array  $bindarray         Array with values for a prepared
     *                                  statement
     * @param bool   $simplifyoneresult If there is only one record or none,
     *                                  it simplifies the return of the
     *                                  function by removing the item
     *                                  from the array.
     *
     * @return array Returns the result
     */
    public function select(string $query, array $bindarray = null, bool $simplifyoneresult = false): array
    {
        $this->connect();

        try {
            $this->connection->beginTransaction();

            $queryreturn = $this->connection->prepare($query);

            if ($bindarray !== null) {
                if (is_array($bindarray)) {
                    foreach ($bindarray as $bak => $bav) {
                        $queryreturn->bindValue(":" . $bak, $bav);
                    }
                } else {
                    $this->connection->rollback();

                    $this->disconnect();
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister("Error! The variable bindarray is not an array", "app/sys");
                }
            }

            $queryreturn->execute();

            $this->connection->commit();

            $result = $queryreturn->fetchAll(\PDO::FETCH_ASSOC);

            if ($simplifyoneresult) {
                if (count($result) <= 1 && (!(empty($result)))) {
                    $result = $result[0];
                }
            }

            $this->disconnect();

            return $result;
        } catch (\PDOException $e) {
            if (!is_array($bindarray)) {
                $bindarray = [];
            }

            $this->connection->rollback();

            $this->disconnect();
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Error querying: %" . $query . "%. " .
                "Message: %" . $e->getMessage() . "%" .
                " / bindArray: %" . implode(",", $bindarray) . "%",
                "app/sys"
            );
        }
    }

    /**
     * Function that allows deleting, inserting or updating database data
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Model
     *
     * @param string $query     Query containing delete
     * @param array  $bindarray Array with values for a prepared statement
     *
     * @return int Returns the number of rows affected.
     */
    public function execute(string $query, array $bindarray = null): int
    {
        $this->connect();

        try {
            $this->connection->beginTransaction();
            $queryreturn = $this->connection->prepare($query);

            if (!$queryreturn) {
                $error = $this->connection->errorInfo();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister($error);
            }

            if ($bindarray !== null) {
                if (is_array($bindarray)) {
                    foreach ($bindarray as $bak => $bav) {
                        $queryreturn->bindValue(":" . $bak, $bav . "");
                    }
                } else {
                    $this->connection->rollback();

                    $this->disconnect();
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister("Error! The bindarray variable is not an array!", "app/sys");
                }
            }

            try {
                $queryreturn->execute();

                $rowsAffected = 0;

                while ($queryreturn->nextRowset()) {
                    /*
                      Adding result to the number of rows affected
                      @see https://bugs.php.net/bug.php?id=61613
                     */
                    $rowsAffected += $queryreturn->rowCount();
                };

                $rowsAffected += $queryreturn->rowCount();

                if (!$queryreturn) {
                    $error = $this->connection->errorInfo();
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister($error);
                }
            } catch (\Exception $e) {
                $this->connection->rollback();
                $this->disconnect();
                return ($e);
            }

            $this->connection->commit();

            $this->disconnect();

            return $rowsAffected;
        } catch (Exception $e) {
            $this->connection->rollBack();
            $this->disconnect();
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Error querying: %" . $query . "%. " .
                "Message: %" . $e->getMessage() . "%" .
                " / bindArray: %" . implode(",", $bindarray) . "%",
                "app/sys"
            );
        }
    }
}
