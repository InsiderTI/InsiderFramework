<?php
/**
  Arquivo KeyClass\Model
*/

// Namespace das KeyClass
namespace KeyClass;

/**
   KeyClass responsável pela comunicação com o banco de dados
  
   @package KeyClass\Model
   @author Marcello Costa
*/
class Model{

    /* Variáveis para conexão com o banco */
    /** @var string $hostname         Hostname
        @var string $connector        Nome do conector utilizado
        @var string $databasename     Nome do banco de dados
        @var string $username         Usuário de conexão com o banco de dados
        @var string $password         Senha de conexão com o banco de dados
        @var bool   $persistent       Conexão persistente ou não
        @var string $isolationLevel   Nível de isolamento do banco de dados
        @var string $charset          Charset que será utilizado
        @var int    $port             Porta de conexão com o banco de dados
        @var string $dbms             Qual é o DBMS do banco de dados
     */
    public $hostname, $connector, $databasename, $username, $password, $persistent, $isolationLevel, $charset, $port, $dbms;

    /** @var bool $conexao Variável de status conexão com o banco */
    public $conexao=false;

    /**
        Construtor da classe que recebe os parâmetros declarados
     
        @author Marcello Costa

        @package KeyClass\Model
     
        @param  string  $database    Nome do banco de dados
     
        @return void Without return
    */
    function __construct(string $database=null) {
        global $kernelspace;
        $databases = $kernelspace->getVariable('databases', 'insiderFrameworkSystem');
        
        // Se não for informado um database
        if ($database == null) {
            if (defined('BD_APP')) {
                // Pega o database default do framework configurado
                $databasechoice=$databases[BD_APP];
                $this->databasename=$databases[BD_APP]['databasename'];
            }
            else {
                // Exceção
                \KeyClass\Error::i10nErrorRegister("Default database not defined in global database array", 'pack/sys');
            }
        }

        // Se um database foi escolhido
        else {
            if (!isset($databases[$database])) {
                // Exceção
                \KeyClass\Error::i10nErrorRegister("Database not found in global database array: %".$database."%", 'pack/sys');
            }
            $databasechoice = $databases[$database];
            $this->databasename=$databases[$database]['databasename'];
        }

        // Coloca os parãmetros nas variáveis do objeto
        $this->hostname = $databasechoice["hostname"];
        $this->connector = $databasechoice["connector"];

        $this->username = $databasechoice["username"];
        $this->password = $databasechoice["password"];

        // Porta da conexão
        $this->port = $databasechoice["port"];

        // DBMS
        if (isset($databasechoice["dbms"])) {
            $this->dbms=$databasechoice["dbms"];
        }

        // Charset
        $this->charset = $databasechoice["charset"];

        // Isolation level
        $this->isolationLevel = $databasechoice["isolationLevel"];

        // Se a conexão for persistente, chama a conexão
        if ($databasechoice["persistent"]) {
            $this->persistent = true;
            $this->connect();
        }
        else {
            $this->persistent = false;
        }

        // Chamando método construtor personalizado
        if (method_exists($this,'customConstruct')) {
            call_user_func_array(array($this,'customConstruct'),array(serialize($this)));
        }
    }

    /**
        Função para checar se o model está atualmente
        conectado ao banco de dados
     
        @author Marcello Costa

        @package KeyClass\Model
     
        @return  bool  Retorno da verificação
    */
    private function checkConnection() : bool {
        // Se estiver conectado
        if (isset($this->conexao) && $this->conexao !== false) {
            return true;
        }

        // Se não estiver conectado
        return false;
    }

    /**
        Função que efetua a conexão com o banco de dados
     
        @author Marcello Costa

        @package KeyClass\Model
     
        @return void|bool Retorna true apenas para dizer que já está conectado
    */
    private function connect() : bool {
        // Se já estiver conectado
        if ($this->checkConnection()) {
            return true;
        }

        // Criando um array em caso de erro
        $arrayError['hostname']=$this->hostname;
        $arrayError['connector']=$this->connector;
        $arrayError['databasename']=$this->databasename;
        $arrayError['userName']=$this->username;
        $arrayError['password']=$this->password;
        $arrayError['persistent']=$this->persistent;
        $arrayError['dbms']=$this->dbms;

        // Verificando variáveis
        if ($this->hostname === null || $this->connector === null || $this->databasename === null || $this->username === null || $this->password === null || $this->persistent === null) {
            // Exceção
            \KeyClass\Error::i10nErrorRegister("Incomplete parameters of connection with database: %".json_encode($arrayError)."%", 'pack/sys');
        }

        // Requerendo o conector do banco
        $connectorPath = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."php".DIRECTORY_SEPARATOR."DatabaseConnectors".DIRECTORY_SEPARATOR.ucfirst(strtolower($this->connector)).DIRECTORY_SEPARATOR.strtolower($this->connector).".php";
        if (!file_exists($connectorPath)) {
          // Exceção
          \KeyClass\Error::i10nErrorRegister("Database file connector not found: %".ucfirst(strtolower($this->connector)).DIRECTORY_SEPARATOR.strtolower($this->connector)."%", 'pack/sys');
        }

        \KeyClass\FileTree::requireOnceFile($connectorPath);

        // Definindo a classe do conector
        $databaseClass="Modules\DatabaseConnectors\\".ucfirst(strtolower(trim($this->connector)));

        // Conecta no banco de acordo com o tipo
        try{
            // Se a classe do conector não existir
            if (!class_exists($databaseClass."_Connector")) {
              // Exceção
              \KeyClass\Error::i10nErrorRegister("Database connector not detected: %".$databaseClass."_Connector%", 'pack/sys');
            }

            // Instanciando conector
            $databaseClass="\\".$databaseClass."_Connector";
            $connector = new $databaseClass();

            // Conectando ao banco de dados
            return $connector->connect($this);
        }
        catch (\PDOException $e) {
            \KeyClass\Error::i10nErrorRegister("Database connection error: %".$e->getMessage()."%", 'pack/sys');
        }
    }

    /**
        Função que encerra a conexão com o banco
     
        @author Marcello Costa

        @package KeyClass\Model
     
        @return void
    */
    private function disconnect() : void {
        // Se a conexão não for persistente
        if ($this->persistent === false) {
            // Se não estiver conectado, tenta conectar
            if ($this->checkConnection()) {
                try{
                    unset($this->conexao);
                }

                catch (\PDOException $e) {
                    \KeyClass\Error::i10nErrorRegister("Database disconnection error: %".$e->getMessage()."%", 'pack/sys');
                }
            }
        }
    }

    /**
        Função que efetua um Select no banco
     
        @author Marcello Costa

        @package KeyClass\Model
     
        @param  string  $query              Query contendo o Select
        @param  array   $bindarray          Array com valores para um preparated statement
        @param  bool    $simplifyoneresult  Caso exista apenas um registro ou nenhum,
                                            simplifica o retorno da função, removendo
                                            o item do array.
     
        @return  array  Retorna o resultado
    */
    public function select(string $query, array $bindarray=null, bool $simplifyoneresult=false) : array {
        // Conecta ao banco de dados
        $this->connect();

        // Efetuando query
        try{
            // Iniciando a transação
            $this->conexao->beginTransaction();

            $queryreturn=$this->conexao->prepare($query);

            // É um preparated statement
            if ($bindarray !== null) {
                // Se for um array (como esperado)
                if (is_array($bindarray)) {
                    // Para cada valor do bindarray, dá um bind no valor
                    // para definir o preparated statement
                    foreach ($bindarray as $bak => $bav) {
                        $queryreturn->bindValue(":".$bak, $bav);
                    }
                }

                // Erro ! Não é um array
                else {
                    // Desfazendo operações
                    $this->conexao->rollback();

                    // Desconecta do banco de dados
                    $this->disconnect();
                    \KeyClass\Error::i10nErrorRegister("Error! The variable bindarray is not an array", 'pack/sys');
                }
            }

            // Executando query
            $queryreturn->execute();

            // Comitando transação
            $this->conexao->commit();

            // Retorno associativo
            $result=$queryreturn->fetchAll(\PDO::FETCH_ASSOC);

            // Se apenas um resultado (ou nenhum) foi encontrado
            if ($simplifyoneresult) {
                if (count($result) <= 1 && (!(empty($result)))) {
                    $result=$result[0];
                }
            }

            // Desconecta do banco de dados
            $this->disconnect();

            // Retorna o array de resultados
            return $result;
        }

        catch (\PDOException $e) {
            if (!is_array($bindarray)){
                $bindarray = [];
            }

            // Desfazendo operações
            $this->conexao->rollback();

            // Desconecta do banco de dados
            $this->disconnect();
            \KeyClass\Error::i10nErrorRegister("Error querying: %".$query."%. Message: %".$e->getMessage()."% / bindArray: %".implode(",",$bindarray)."%", 'pack/sys');
        }
    }

    /**
        Função que permite deletar, inserir ou atualizar dados do banco
     
        @author Marcello Costa

        @package KeyClass\Model
     
        @param  string  $query    Query contendo o delete
        @param  array   $bindarray Array com valores para um preparated statement
     
        @return  int  Retorno o número de rows afetadas
    */
    public function execute(string $query, array $bindarray=null) : int {
        // Conecta ao banco de dados
        $this->connect();

        // Efetuando query
        try{
            // Iniciando a transação
            $this->conexao->beginTransaction();

            // Inserindo a query no objeto
            $queryreturn=$this->conexao->prepare($query);

            // Se algum erro ocorreu no prepare
            if (!$queryreturn) {
                $error = $this->conexao->errorInfo();
                \KeyClass\Error::errorRegister($error);
            }
            
            // É um preparated statement
            if ($bindarray !== null) {
                // Se for um array (como esperado)
                if (is_array($bindarray)) {
                    // Para cada valor do bindarray, dá um bind no valor
                    // para definir o preparated statement. Sempre
                    // transforma os valores em string pois o PDO
                    // não aceita inteiros e floats.
                    foreach ($bindarray as $bak => $bav) {
                        $queryreturn->bindValue(":".$bak, $bav."");
                    }
                }

                // Erro ! Não é um array
                else {
                    // Desfazendo operações
                    $this->conexao->rollback();

                    // Desconecta do banco de dados
                    $this->disconnect();
                    \KeyClass\Error::i10nErrorRegister("Error! The bindarray variable is not an array!", 'pack/sys');
                }
            }

            // Executando a query
            try{
                // Executando query                
                $queryreturn->execute();
                
                // Contador de rows afetadas
                $rowsAffected = 0;
                
                // Executando múltiplas querys (caso existam)
                while ($queryreturn->nextRowset()) { 
                    /* https://bugs.php.net/bug.php?id=61613 */
                    
                    // Adicionando resultado à quantidade de rows afetadas
                    $rowsAffected += $queryreturn->rowCount();
                };
                
                // Adicionando resultado à quantidade de rows afetadas
                $rowsAffected += $queryreturn->rowCount();
                
                // Se algum erro ocorreu no execute
                if (!$queryreturn) {
                    $error = $this->conexao->errorInfo();
                    \KeyClass\Error::errorRegister($error);
                }
            }
            catch (\Exception $e) {
                // Desfazendo operações
                $this->conexao->rollback();

                // Desconecta do banco de dados
                $this->disconnect();

                // Retornado excessão
                return($e);
            }

            // Gravando alterações
            $this->conexao->commit();

            // Desconecta do banco de dados
            $this->disconnect();
            
            // Retornando número de rows afetadas
            return $rowsAffected;
        }

        // Se algo deu errado
        catch (Exception $e) {
            // Desfazendo operações
            $this->conexao->rollBack();

            // Desconecta do banco de dados
            $this->disconnect();

            // Exceção
            \KeyClass\Error::i10nErrorRegister("Error querying: %".$query."%. Message: %".$e->getMessage()."% / bindArray: %".implode(",",$bindarray)."%", 'pack/sys');
        }
    }
}
