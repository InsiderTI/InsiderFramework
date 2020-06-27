<?php

namespace Models\sys;

/**
 * Class responsible for managing users in the database
 * of the framework
 *
 * @author Marcello Costa
 *
 * @package Models\sys\UserAdminModel
 */
class UserAdminModel extends \Modules\InsiderFramework\Core\Model
{
    /**
     * Updates a user's password reset token
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $email User email
     * @param string $token User token
     *
     * @return bool Return from operation
     */
    public function updateResetTokenPassword(string $email, string $token): bool
    {
        $query = "UPDATE users SET "
            . "RESETPASSWDTOKEN = :token "
            . "WHERE LOGIN = :email"
            . ";";

        $bindarray = array(
            'token' => $token,
            'email' => $email
        );

        $result = $this->execute($query, $bindarray);

        if ($result !== null) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister(
                'Error performing update of user reset token'
            );
        }

        return true;
    }

    /**
     * Checks the validity of a password reset token
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $email User email
     * @param string $token User token
     *
     * @return bool Check return
     */
    public function checkResetToken(string $email, string $token): bool
    {
        // Retrieves user information
        $userdata = $this->recoveryUserData($email);

        // If the information exists and it is valid
        if (isset($userdata['resetpasswdtoken']) and $userdata['resetpasswdtoken'] == $token) {
            return true;
        }

        return false;
    }

    /**
     * Recovers user data
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $login User login
     * @param int    $id    User ID
     *
     * @return array Recovered data
     */
    public function recoveryUserData(string $login = null, int $id = null): array
    {
        if ($login !== null) {
            $queryuserdata = "SELECT * from users s INNER JOIN "
                . "rel_users_groups rug on (s.ID "
                . "= rug.USERID) INNER JOIN login_registry lr on (lr.USERID = s.ID) WHERE s.LOGIN = :login LIMIT 1";

            $bindarray = array(
                'login' => $login
            );
        } else {
            $queryuserdata = "SELECT * from users s INNER JOIN "
                . "rel_users_groups rug on (s.ID "
                . "= rug.USERID) INNER JOIN login_registry lr on (lr.USERID = s.ID) WHERE s.ID = :id LIMIT 1";

            $bindarray = array(
                'id' => $id
            );
        }

        $statususer = $this->select($queryuserdata, $bindarray, true);

        if ($statususer !== null) {
            return $statususer;
        }

        return [];
    }

    /**
     * Modifies the user's access password
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $login   User email
     * @param string $newpass New password
     *
     * @return bool Processing result
     */
    public function updatePassUser(string $login, string $newpass): bool
    {
        $queryuserdata = "SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        $idusertmp = $this->select($queryuserdata, $bindarray, true);

        if (empty($idusertmp)) {
            return false;
        } else {
            $iduser = $idusertmp['ID'];
        }

        $deletequery = "UPDATE login_registry SET "
            . "KEYCOOKIE = '' "
            . "WHERE USERID = :iduser;";

        $bindarray = array(
            'iduser' => $iduser
        );

        $result = $this->execute($deletequery, $bindarray);

        if ($result !== null) {
            return false;
        }

        $updatequery = "UPDATE users SET "
            . "PASSWORD = :password "
            . "WHERE LOGIN = :login;";

        $bindarray = array(
            'password' => $newpass,
            'login' => $login
        );

        $result = $this->execute($updatequery, $bindarray);

        if ($result !== null) {
            return false;
        } else {
            return true;
        }
    }

    /**************************************/
    // Code not reviewed below
    /**************************************/

    /**
     * Cadastro um usuário no banco de dados
     *
     * @todo Revisar código da função
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $login    Login do usuário
     * @param string $password Senha do usuário
     * @param array  $groups   ID dos grupos ao qual o usuário pertence
     *
     * @return bool Processing result de cadastro
     */
    public function addUser(string $login, string $password, array $groups = null): bool
    {
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
        $resultinsertuser = $this->execute($query, $bindarray);

        // Erro ao inserir novo usuário
        if ($resultinsertuser !== null) {
            return false;
        }

        // Recuperando ID recém inserido
        $queryuserid = "SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        // Retorno do status do usuário
        $useridtmp = $this->select($queryuserid, $bindarray, true);

        // Usuário não encontrado
        if (empty($useridtmp)) {
            return false;
        } else {
            $userid = $useridtmp['ID'];
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
                $resultinsertrel = $this->execute($query, $bindarray);

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
     * Atualiza o cadastro de um usuário no banco de dados
     *
     * @todo Revisar código da função
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $oldlogin Login atual do usuário
     * @param string $newlogin Novo Login do usuário
     * @param string $password Senha do usuário
     * @param array  $groups   ID dos grupos ao qual o usuário pertence
     *
     * @return bool Retorno da operação
     */
    public function updateUser(
        string $oldlogin,
        string $newlogin = null,
        string $password = null,
        array $groups = null
    ): bool {
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
            $login = $newlogin;
        } else {
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
                $login = $newlogin;
            } else {
                $query = "UPDATE users SET "
                    . "PASSWORD = :password "
                    . "WHERE LOGIN = :oldlogin;";

                $bindarray = array(
                    'oldlogin' => $oldlogin,
                    'password' => $password,
                );

                // Login
                $login = $oldlogin;
            }
        }

        // Se não foi atualizado nada, então a variável bindarray não será preenchida.
        // Apesar de não ter sido atualizado nada, o processo foi concluído com sucesso
        if (!isset($bindarray)) {
            return true;
        } else {
            // Atualizando tabela de users de acordo com os parâmetros
            // acima especificados
            $resultupdateuser = $this->execute($query, $bindarray);

            // Erro ao atualizar registros
            if ($resultupdateuser !== null) {
                return false;
            }

            // Apagando registro de login no banco (deslogando usuário)
            $deletequery = "update login_registry set KEYCOOKIE = '' where USERID in (
                select `ID` from (select `ID` from `users` where LOGIN = :login) as updatetable
            )";

            $bindarray = array(
                'login' => $login
            );

            $result = $this->execute($deletequery, $bindarray, true);

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
        $queryuserid = "SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        // Retorno do status do usuário
        $useridtmp = $this->select($queryuserid, $bindarray, true);

        // Usuário não encontrado
        if (empty($useridtmp)) {
            return false;
        } else {
            // Definindo ID do usuário
            $userid = $useridtmp['ID'];
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
            $resultdeleterel = $this->execute($query, $bindarray);

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
                    $resultinsertrel = $this->execute($query, $bindarray);

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
     * Loga um usuário no banco do framework
     *
     * @todo Revisar código da função
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $login       Login do usuário
     * @param string $valuecookie Valor do cookie de identificação
     *
     * @return bool Processing result
     */
    public function login(string $login, string $valuecookie): bool
    {
        // Recuperando ID do usuário
        $queryuserid = "SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        // Retorno do status do usuário
        $useridtmp = $this->select($queryuserid, $bindarray, true);

        // Usuário não encontrado
        if (empty($useridtmp)) {
            return false;
        } else {
            $userid = $useridtmp['ID'];
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
        $resultinsertreg = $this->execute($query, $bindarray);

        // Erro ao inserir novo usuário
        if ($resultinsertreg !== null) {
            return false;
        }

        // Inserção concluída
        return true;
    }

    /**
     * Desloga um usuário no banco do framework
     *
     * @todo Revisar código da função
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $login Login do usuário
     *
     * @return bool Processing result
     */
    public function logout(string $login): bool
    {
        // Recuperando ID do usuário
        $queryuserid = "SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        // Retorno do status do usuário
        $useridtmp = $this->select($queryuserid, $bindarray, true);

        // Usuário não encontrado
        if (empty($useridtmp)) {
            return false;
        } else {
            $userid = $useridtmp['ID'];
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
        $result = $this->execute($deletequery, $bindarray);

        // Erro ao remover usuário do registro de login
        if ($result !== null) {
            return false;
        }

        // Remoção concluída
        return true;
    }
}
