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
     * Register a user in the database
     *
     * @todo Review function code
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $login    Login do usuário
     * @param string $password User password
     * @param array  $groups   ID of the groups to which the user belongs
     *
     * @return bool Processing result of registration
     */
    public function addUser(string $login, string $password, array $groups = null): bool
    {
        $query = "INSERT INTO users SET "
            . "LOGIN = :email, "
            . "PASSWORD = :senha"
            . ";";

        $bindarray = array(
            'email' => $login,
            'senha' => $password,
        );

        $resultinsertuser = $this->execute($query, $bindarray);

        if ($resultinsertuser !== null) {
            return false;
        }

        $queryuserid = "SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        $useridtmp = $this->select($queryuserid, $bindarray, true);

        if (empty($useridtmp)) {
            return false;
        } else {
            $userid = $useridtmp['ID'];
        }

        if ($groups !== null && count($groups) !== 0) {
            foreach ($groups as $g) {
                $query = "INSERT INTO rel_users_groups SET "
                    . "USERID = :userid, "
                    . "GROUPID = :group"
                    . ";";

                $bindarray = array(
                    'userid' => $userid,
                    'group' => $g,
                );

                $resultinsertrel = $this->execute($query, $bindarray);

                if ($resultinsertrel !== null) {
                    return false;
                }
            }

            return true;
        }

        return true;
    }

    /**
     * Updates a user's registration in the database
     *
     * @todo Review function code
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $oldlogin Current user login
     * @param string $newlogin New user Login
     * @param string $password User password
     * @param array  $groups   ID of the groups to which the user belongs
     *
     * @return bool Return from operation
     */
    public function updateUser(
        string $oldlogin,
        string $newlogin = null,
        string $password = null,
        array $groups = null
    ): bool {
        if ($password === null) {
            if ($newlogin !== null && ($oldlogin != $newlogin)) {
                $query = "UPDATE users SET "
                    . "LOGIN = :newlogin "
                    . "WHERE LOGIN = :oldlogin;";

                $bindarray = array(
                    'oldlogin' => $oldlogin,
                    'newlogin' => $newlogin
                );
            }

            $login = $newlogin;
        } else {
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

                $login = $newlogin;
            } else {
                $query = "UPDATE users SET "
                    . "PASSWORD = :password "
                    . "WHERE LOGIN = :oldlogin;";

                $bindarray = array(
                    'oldlogin' => $oldlogin,
                    'password' => $password,
                );

                $login = $oldlogin;
            }
        }

        if (!isset($bindarray)) {
            return true;
        } else {
            $resultupdateuser = $this->execute($query, $bindarray);

            if ($resultupdateuser !== null) {
                return false;
            }

            $deletequery = "update login_registry set KEYCOOKIE = '' where USERID in (
                select `ID` from (select `ID` from `users` where LOGIN = :login) as updatetable
            )";

            $bindarray = array(
                'login' => $login
            );

            $result = $this->execute($deletequery, $bindarray, true);

            if ($result !== null) {
                return false;
            }
        }

        if ($resultupdateuser !== null) {
            return false;
        }

        $queryuserid = "SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        $useridtmp = $this->select($queryuserid, $bindarray, true);

        if (empty($useridtmp)) {
            return false;
        } else {
            $userid = $useridtmp['ID'];
        }

        if ($groups !== null) {
            $query = "DELETE FROM rel_users_groups WHERE "
                . "USERID = :userid"
                . ";";

            $bindarray = array(
                'userid' => $userid,
            );

            $resultdeleterel = $this->execute($query, $bindarray);

            if ($resultdeleterel !== null) {
                return false;
            }

            if (count($groups) !== 0) {
                foreach ($groups as $g) {
                    $query = "INSERT INTO rel_users_groups SET "
                        . "USERID = :userid, "
                        . "GROUPID = :group"
                        . ";";

                    $bindarray = array(
                        'userid' => $userid,
                        'group' => $g,
                    );

                    $resultinsertrel = $this->execute($query, $bindarray);

                    if ($resultinsertrel !== null) {
                        return false;
                    }
                }
            }

            return true;
        }

        return true;
    }

    /**
     * Logs a user into the framework database
     *
     * @todo Review function code
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $login       User login
     * @param string $valuecookie Identification cookie value
     *
     * @return bool Processing result
     */
    public function login(string $login, string $valuecookie): bool
    {
        $queryuserid = "SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        $useridtmp = $this->select($queryuserid, $bindarray, true);

        if (empty($useridtmp)) {
            return false;
        } else {
            $userid = $useridtmp['ID'];
        }

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

        $resultinsertreg = $this->execute($query, $bindarray);

        if ($resultinsertreg !== null) {
            return false;
        }

        return true;
    }

    /**
     * Log a user out of the framework database
     *
     * @todo Revisar código da função
     *
     * @author Marcello Costa
     *
     * @package Models\sys\UserAdmin_Model
     *
     * @param string $login User login
     *
     * @return bool Processing result
     */
    public function logout(string $login): bool
    {
        $queryuserid = "SELECT ID from users WHERE LOGIN = :login LIMIT 1";

        $bindarray = array(
            'login' => $login
        );

        $useridtmp = $this->select($queryuserid, $bindarray, true);

        if (empty($useridtmp)) {
            return false;
        } else {
            $userid = $useridtmp['ID'];
        }

        $deletequery = "UPDATE login_registry SET "
            . "KEYCOOKIE = '' "
            . "WHERE USERID = :userid;";

        $bindarray = array(
            'userid' => $userid
        );

        $result = $this->execute($deletequery, $bindarray);

        if ($result !== null) {
            return false;
        }

        return true;
    }
}
