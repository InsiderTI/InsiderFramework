<?php

namespace Modules\InsiderFramework\Core\RoutingSystem;

/**
 * Permission handling class
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Permission
 *
 * @author Marcello Costa
 */
class Permission
{
    /**
     * Standard function of the framework for security checking. More details
     * on how to use this function in the documentation.
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Permission
     *
     * @return mixed Array of data retrieved from the database or string 'UNPRIVILEGEDUSER'
     */
    public static function getNativeAccessLevel()
    {
        $server = \Modules\InsiderFramework\Core\KernelSpace::getVariable('SERVER', 'insiderFrameworkSystem');
        $requestSource = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'requestSource',
            'insiderFrameworkSystem'
        );

        $securitymodel = new \Modules\InsiderFramework\Core\Model();

        if (isset($server['HTTP_USER_AGENT']) && $requestSource === 'console') {
            if (isset($server['QUERY_STRING']) && (strpos($server['QUERY_STRING'], 'cookieframeidsession') !== false)) {
                preg_match("/cookieframeidsession=([^&]*)/", $server['QUERY_STRING'], $matches);

                \Modules\InsiderFramework\Core\Manipulation\Cookie::setCookie("idsession", $matches[1]);
                $cookie = $matches[1];
            }
        } else {
            $cookie = \Modules\InsiderFramework\Core\Manipulation\Cookie::getCookie('idsession');
        }

        if ($cookie === null) {
            return 'UNPRIVILEGEDUSER';
        } else {
            $query = "select USERID from login_registry where KEYCOOKIE = :keycookie";
            $bindarray = array(
                'keycookie' => $cookie
            );

            $useridr = $securitymodel->select($query, $bindarray, true);

            if (!(empty($useridr))) {
                if (!(isset($useridr['USERID']))) {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                        'Error ! There are two users with the same access cookie!'
                    );
                }

                $permissionsArray = array();
                $permissionsArray['USERID'][] = $useridr['USERID'];

                $query = "select GROUPID from rel_users_groups where USERID = :userid";
                $bindarray = array(
                    'userid' => $useridr['USERID']
                );
                $usergroups = $securitymodel->select($query, $bindarray, true);

                $permissionsArray['USERGROUPS'] = [];

                foreach ($usergroups as $ugk => $ugv) {
                    $permissionsArray['USERGROUPS'][] = $ugv;
                }

                return $permissionsArray;
            } else {
                // Sem permissões para qualquer operação
                return 'UNPRIVILEGEDUSER';
            }
        }
    }

    /**
     * Função que verifica a permissão do usuário atual utilizando
     * a lógica do ACL "native"
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Permission
     *
     * @param \Modules\InsiderFramework\Core\RoutingSystem\RouteData $routeObj      Objeto de rota
     * @param array                                   $currentUserAclPermission Permissões atuais
     * @param bool                                    $access        Booleano que indica se o
     *                                                               usuário tem acesso ou não
     *                                                               à rota atual
     *
     * @return void
     */
    public static function validateNativeACLPermission(
        \Modules\InsiderFramework\Core\RoutingSystem\RouteData $routeObj,
        array $currentUserAclPermission,
        bool &$access
    ): void {
        $permissionsOfRoute = $routeObj->getPermissions();

        // Verificando permissões da rota
        // A regra de permissões de rota funciona da seguinte forma
        // 1° - Tratar todos os grupos listados
        // 2° - Modificar os grupos tratados de acordo com os usuários listados

        // Rota - Permissão de usuários
        $ut = $permissionsOfRoute['users']['type'];

        // Se existirem permissões de usuários listados
        if ($permissionsOfRoute['users']['usersID'] !== "") {
            $uid = explode(",", $permissionsOfRoute['users']['usersID']);
            foreach ($uid as $k => $u) {
                $uid[$k] = intval($u);
            }
        } else {
            $uid = [];
        }

        // O usuário está logado ?
        if ($currentUserAclPermission !== "UNPRIVILEGEDUSER") {
            $pnowu = $currentUserAclPermission['USERID'];
        } else {
            $pnowu = $currentUserAclPermission;
        }

        // Rota - Permissão de grupos
        $gt = $permissionsOfRoute['groups']['type'];

        // Se existirem permissões de grupos listados
        if ($permissionsOfRoute['groups']['groupsID'] !== "") {
            $gid = explode(",", $permissionsOfRoute['groups']['groupsID']);
            foreach ($gid as $k => $g) {
                $gid[$k] = intval($g);
            }
        } else {
            $gid = [];
        }

        // Armazenando permissão atual do grupo
        if ($currentUserAclPermission === null) {
            $currentUserAclPermission = "UNPRIVILEGEDUSER";
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                array(
                    'currentUserAclPermission' => $currentUserAclPermission
                ),
                'routingSystem'
            );
        }
        if ($currentUserAclPermission !== "UNPRIVILEGEDUSER") {
            if (!is_array($currentUserAclPermission)) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Expected array on return from permissions check function",
                    "app/sys"
                );
            }

            if (
                !isset($currentUserAclPermission['USERGROUPS']) ||
                !isset($currentUserAclPermission['USERID']) ||
                !is_array($currentUserAclPermission['USERGROUPS']) ||
                !is_array($currentUserAclPermission['USERID'])
            ) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Array on return of invalid permissions check function",
                    "app/sys"
                );
            }

            // Para cada item de grupo encontrado
            // constrói o array de grupos
            $pnowg = array();
            foreach ($currentUserAclPermission['USERGROUPS'] as $pkg => $pkv) {
                if (isset($pkv['GROUPID'])) {
                    $pnowg[] = intval($pkv['GROUPID']);
                } else {
                    $pnowg[] = intval($pkv);
                }
            }
        } else {
            $pnowg = $currentUserAclPermission;
        }

        // Flag de acesso ao usuário atual
        $access = false;

        // Existem restrições de grupos na rota ?
        if (count($gid) !== 0 && $gid !== "") {
            // O usuário está logado
            if ($pnowg != "UNPRIVILEGEDUSER") {
                // O usuário pertence a algum grupo ?
                if (is_array($pnowg)) {
                    // Intercessão de usuários na regra
                    $inc_g = array_intersect($gid, $pnowg);

                    // As permissões de grupo são exclusão ou inclusão ?
                    switch ($gt) {
                        case "include":
                            // Se o usuário esta em algum desses grupos
                            if (count($inc_g) !== 0) {
                                // Autorizado
                                $access = true;
                            } else {
                                // Não autorizado
                                $access = false;
                            }
                            break;

                        case "exclude":
                            // Se o usuário esta em algum desses grupos
                            if (count($inc_g) !== 0) {
                                // Autorizado
                                $access = false;
                            } else {
                                // Não autorizado
                                $access = true;
                            }
                            break;

                        default:
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                                "Permissions error on the route " . $route
                            );
                            break;
                    }
                } else {
                    // Não autorizado
                    $access = false;
                }
            } else {
                // Não autorizado
                $access = false;
            }
        }

        // Existem restrições de usuários na rota ?
        if (count($uid) !== 0) {
            // O usuário está logado
            if ($pnowu != "UNPRIVILEGEDUSER") {
                // Intercessão de usuários na regra
                $inc_u = array_intersect($uid, $pnowu);

                // As permissões de grupo são exclusão ou inclusão ?
                switch ($ut) {
                    case "include":
                        // Se o usuário é algum destes listados
                        if (count($inc_u) !== 0) {
                            // Autorizado
                            $access = true;
                        } else {
                            // Não autorizado
                            $access = false;
                        }
                        break;

                    case "exclude":
                        // Se o usuário é algum destes listados
                        if (count($inc_u) !== 0) {
                            // Não autorizado
                            $access = false;
                        } else {
                            // Autorizado
                            $access = true;
                        }
                        break;

                    default:
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                            "Permissions error on the route " . $route
                        );
                        break;
                }
            } else {
                // Não autorizado
                $access = false;
            }
        }
    }
}
