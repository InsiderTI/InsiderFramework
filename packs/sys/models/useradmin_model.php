<?php
/**
  Model para gerenciamento de usuários
*/

// Namespace relativo ao pack do model
namespace Models\sys;

/**
  Classe responsável pelo gerenciamento de usuários no banco de dados
  do framework
  
  @author Marcello Costa
  @package Models\sys\UserAdmin_Model
*/
class UserAdmin_Model extends \KeyClass\Model{
    /**
        Atualiza o token de reset do password de um usuário
     
        @author Marcello Costa
        @package Models\sys\UserAdmin_Model
     
        @param  String  $email    Email do usuário
        @param  String  $token    Token do usuário
      
        @return  Bool  Retorno da operação
    */
    function UpdateResetTokenPassword(string $email, string $token) {
        $query = "UPDATE users SET "
                . "RESETPASSWDTOKEN = :token "
                . "WHERE LOGIN = :email"
                . ";";

        $bindarray = array(
            'token' => $token,
            'email' => $email
        );

        // Realizando update
        $resultuser = $this->execute($query, $bindarray);

        // Erro
        if ($resultuser !== null) {
            \KeyClass\Error::errorRegister('Error performing update of user reset token');
        }
        
        // Retornando o sucesso
        return true;
    }
    
    /**
        Verifica a validade de um token de reset de senha
     
        @author Marcello Costa
        @package Models\sys\UserAdmin_Model
     
        @param  String  $email    Email do usuário
        @param  String  $token    Token do usuário
      
        @return  Bool  Retorno da checagem
    */
    function CheckResetToken(string $email, string $token) {
        // Recupera informações do usuário
        $userdata=$this->RecoveryUserData($email);
        
        // Se existir a informação e ela for válida
        if (isset($userdata['resetpasswdtoken']) and $userdata['resetpasswdtoken'] == $token) {
            return true;
        }
        
        // Se algo não confere
        else {
            return false;
        }
    }
    
    /**
        Recupera os dados do usuário
     
        @author Marcello Costa
        @package Models\sys\UserAdmin_Model
     
        @param  string  $login    Login do usuário
        @param  int     $id       ID do usuário
      
        @return  array  Dados recuperados
    */
    function RecoveryUserData(string $login=null, int $id=null) {
        // Se for uma busca por login
        if ($login !== NULL) {
            $queryuserdata="SELECT * from users s INNER JOIN "
                 ."rel_users_groups rug on (s.ID "
                 ."= rug.USERID) INNER JOIN login_registry lr on (lr.USERID = s.ID) WHERE s.LOGIN = :login LIMIT 1";

            $bindarray = array(
                'login' => $login
            );
        }
        
        // Se for uma busca por ID
        else {
            $queryuserdata="SELECT * from users s INNER JOIN "
                 ."rel_users_groups rug on (s.ID "
                 ."= rug.USERID) INNER JOIN login_registry lr on (lr.USERID = s.ID) WHERE s.ID = :id LIMIT 1";

            $bindarray = array(
                'id' => $id
            );
        }

        // Retorno do status do usuário
        $statususer=$this->select($queryuserdata, $bindarray, true);
        
        // Retornando dados
        if ($statususer !== NULL) {
            return $statususer;
        }
        // Sem resultados encontrados
        else {
            return false;
        }
    }
    
    /**
        Modifica a senha de acesso do usuário
     
        @author Marcello Costa
        @package Models\sys\UserAdmin_Model
     
        @param  String  $login      Email do usuário
        @param  String  $newpass    Nova senha
      
        @return  Bool  Processing result
    */
    function UpdatePassUser(string $login, string $newpass) {
        // Apagando sessão do usuário que está
        // talvez logado

        // Recuperando ID do usuário
        $queryuserdata="SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        // Gravando ação
        $idusertmp=$this->select($queryuserdata, $bindarray, true);
        
        // Usuário não encontrado
        if (empty($idusertmp)) {
            return false;
        }
        else {
            $iduser=$idusertmp['ID'];
        }
        
        // Apagando registro no banco
        $deletequery = "UPDATE login_registry SET "
                     . "KEYCOOKIE = '' "
                     . "WHERE USERID = :iduser;";
        
        $bindarray = array(
            'iduser' => $iduser
        );

        // Gravando ação
        $result=$this->execute($deletequery, $bindarray);
        
        // Se deu algum erro
        if ($result !== null) {
            return false;
        }
        
        // Atualizando senha no banco da aplicação
        $updatequery = "UPDATE users SET "
                     . "PASSWORD = :password "
                     . "WHERE LOGIN = :login;";
        
        $bindarray = array(
            'password' => $newpass,
            'login' => $login
        );

        // Gravando ação
        $result=$this->execute($updatequery, $bindarray);
        
        // Se deu algum erro
        if ($result !== null) {
            return false;
        }
        else {
            return true;
        }
    }
    
    /**************************************/
    // Código abaixo não revisado
    /**************************************/
    
    /**
        Cadastro um usuário no banco de dados

        @author Marcello Costa
        @package Models\sys\UserAdmin_Model
     
        @todo Revisar código da função
     
        @param  string  $login       Login do usuário
        @param  string  $password    Senha do usuário
        @param  array   $groups      ID dos grupos ao qual o usuário pertence
      
        @return  bool  Processing result de cadastro
    */
    function AddUser(string $login, string $password, array $groups=null) {
        // Tabela users
        $query = "INSERT INTO users SET "
                        . "LOGIN = :email, "
                        . "PASSWORD = :senha"
                        . ";";
        
        $bindarray = array(
            'email' => $login,
            'senha' => $password,
        );
        
        // Tabela users
        $resultinsertuser=$this->execute($query, $bindarray);

        // Erro ao inserir novo usuário
        if ($resultinsertuser !== null) {
            return false;
        }
        
        // Recuperando ID recém inserido
        $queryuserid="SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        // Retorno do status do usuário
        $useridtmp=$this->select($queryuserid, $bindarray, true);

        // Usuário não encontrado
        if (empty($useridtmp)) {
            return false;
        }
        else {
            $userid=$useridtmp['ID'];
        }

        // Se existirem grupos
        if ($groups !== null && count($groups) !== 0) {
            // Inserindo registros para cada grupo
            foreach ($groups as $g) {
                // Tabela rel_users_groups
                $query = "INSERT INTO rel_users_groups SET "
                                . "USERID = :userid, "
                                . "GROUPID = :group"
                                . ";";

                $bindarray = array(
                    'userid' => $userid,
                    'group' => $g,
                );
                
                // Tabela rel_users_groups
                $resultinsertrel=$this->execute($query, $bindarray);

                // Erro ao inserir novo usuário
                if ($resultinsertrel !== null) {
                    return false;
                }
            }
            
            // Inserções feitas com sucesso
            return true;
        }
        
        // Inserção feita com sucesso
        return true;
    }

    /**
        Atualiza o cadastro de um usuário no banco de dados
     
        @author Marcello Costa
        @package Models\sys\UserAdmin_Model
     
        @todo Revisar código da função
     
        @param  string  $oldlogin    Login atual do usuário
        @param  string  $newlogin    Novo Login do usuário
        @param  string  $password    Senha do usuário
        @param  array   $groups      ID dos grupos ao qual o usuário pertence
      
        @return  bool  Retorno da operação
    */
    function UpdateUser(string $oldlogin, string $newlogin=null, string $password=null, array $groups=null) {
        // Tabela users
        // Se o password não foi atualizado
        if ($password === null) {
            // Login sendo atualizado
            if ($newlogin !== null && ($oldlogin != $newlogin)) {
                $query = "UPDATE users SET "
                                . "LOGIN = :newlogin "
                                . "WHERE LOGIN = :oldlogin;";

                $bindarray = array(
                    'oldlogin' => $oldlogin,
                    'newlogin' => $newlogin
                );
            }
            
            // Novo login
            $login=$newlogin;
        }
        
        // Senha sendo atualizada
        else {
            // Login sendo atualizado com senha
            if ($newlogin !== null && ($oldlogin != $newlogin)) {
                $query = "UPDATE users SET "
                                . "LOGIN = :newlogin, "
                                . "PASSWORD = :password "
                                . "WHERE LOGIN = :oldlogin;";

                $bindarray = array(
                    'oldlogin' => $oldlogin,
                    'newlogin' => $newlogin,
                    'password' => $password,
                );
                
                // Novo login
                $login=$newlogin;
            }
            
            // Mantendo login
            else {
                $query = "UPDATE users SET "
                                . "PASSWORD = :password "
                                . "WHERE LOGIN = :oldlogin;";

                $bindarray = array(
                    'oldlogin' => $oldlogin,
                    'password' => $password,
                );
                
                // Login
                $login=$oldlogin;
            }
        }
        
        // Se não foi atualizado nada, então a variável bindarray não será preenchida.
        // Apesar de não ter sido atualizado nada, o processo foi concluído com sucesso
        if (!isset($bindarray)) {
            return true;
        }
        
        // Se alguma modificação deve ser realizada
        else {
            // Atualizando tabela de users de acordo com os parâmetros
            // acima especificados
            $resultupdateuser=$this->execute($query, $bindarray);
        
            // Erro ao atualizar registros
            if ($resultupdateuser !== null) {
                return false;
            }
            
            // Apagando registro de login no banco (deslogando usuário)
            $deletequery="update login_registry set KEYCOOKIE = '' where USERID in (
                select `ID` from (select `ID` from `users` where LOGIN = :login) as updatetable
            )";

            $bindarray = array(
                'login' => $login
            );

            $result=$this->execute($deletequery, $bindarray, true);
            
            // Erro ao atualizar registros de login
            if ($result !== null) {
                return false;
            }
        }

        // Erro ao atualizar usuário
        if ($resultupdateuser !== null) {
            return false;
        }
        
        // Recuperando ID recém inserido
        $queryuserid="SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        // Retorno do status do usuário
        $useridtmp=$this->select($queryuserid, $bindarray, true);

        // Usuário não encontrado
        if (empty($useridtmp)) {
            return false;
        }
        
        // Usuário encontrado
        else {
            // Definindo ID do usuário
            $userid=$useridtmp['ID'];
        }

        // Se existirem grupos listados ou um array vazio
        if ($groups !== null) {
            // Deletando registros de grupo antigos
            $query = "DELETE FROM rel_users_groups WHERE "
                            . "USERID = :userid"
                            . ";";

            $bindarray = array(
                'userid' => $userid,
            );

            // Tabela rel_users_groups
            $resultdeleterel=$this->execute($query, $bindarray);

            // Erro ao apagar grupos
            if ($resultdeleterel !== null) {
                return false;
            }
            
            // Se foram especificados grupos
            if (count($groups) !== 0) {
                // Inserindo registros para cada grupo
                foreach ($groups as $g) {
                    // Tabela rel_users_groups
                    $query = "INSERT INTO rel_users_groups SET "
                                    . "USERID = :userid, "
                                    . "GROUPID = :group"
                                    . ";";

                    $bindarray = array(
                        'userid' => $userid,
                        'group' => $g,
                    );

                    // Tabela rel_users_groups
                    $resultinsertrel=$this->execute($query, $bindarray);

                    // Erro ao inserir novo usuário
                    if ($resultinsertrel !== null) {
                        return false;
                    }
                }
            }
            
            // Inserções feitas com sucesso
            return true;
        }
        
        // Atualização feita com sucesso
        return true;
    }

    /**
        Loga um usuário no banco do framework
     
        @author Marcello Costa
        @package Models\sys\UserAdmin_Model
     
        @todo Revisar código da função
     
        @param  string  $login          Login do usuário
        @param  string  $valuecookie    Valor do cookie de identificação
      
        @return  bool  Processing result
    */
    function Login(string $login, string $valuecookie) {
        // Recuperando ID do usuário
        $queryuserid="SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        // Retorno do status do usuário
        $useridtmp=$this->select($queryuserid, $bindarray, true);

        // Usuário não encontrado
        if (empty($useridtmp)) {
            return false;
        }
        else {
            $userid=$useridtmp['ID'];
        }
        
        // Tabela login_registry
        // Insere ou dá update (se já existir)
        $query = "INSERT INTO login_registry
                        (USERID, KEYCOOKIE)
                    VALUES
                        (:userid, :valuecookie)
                    ON DUPLICATE KEY UPDATE
                        USERID     = VALUES(USERID),
                        KEYCOOKIE = VALUES(KEYCOOKIE)
                    ;";

        $bindarray = array(
            'userid' => $userid,
            'valuecookie' => $valuecookie
        );

        // Tabela rel_users_groups
        $resultinsertreg=$this->execute($query, $bindarray);

        // Erro ao inserir novo usuário
        if ($resultinsertreg !== null) {
            return false;
        }
        
        // Inserção concluída
        return true;
    }

    /**
        Desloga um usuário no banco do framework
     
        @author Marcello Costa
        @package Models\sys\UserAdmin_Model
     
        @todo Revisar código da função
     
        @param  string  $login   Login do usuário
      
        @return  bool  Processing result
    */
    function Logout(string $login) {
        // Recuperando ID do usuário
        $queryuserid="SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        // Retorno do status do usuário
        $useridtmp=$this->select($queryuserid, $bindarray, true);

        // Usuário não encontrado
        if (empty($useridtmp)) {
            return false;
        }
        else {
            $userid=$useridtmp['ID'];
        }
        
        // Tabela login_registry
        // Removendo registro do usuário do banco
        $deletequery = "UPDATE login_registry SET "
             . "KEYCOOKIE = '' "
             . "WHERE USERID = :userid;";
        
        $bindarray = array(
            'userid' => $userid
        );

        // Tabela rel_users_groups
        $result=$this->execute($deletequery, $bindarray);

        // Erro ao remover usuário do registro de login
        if ($result !== null) {
            return false;
        }
        
        // Remoção concluída
        return true;
    }
}
