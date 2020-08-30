<?php

namespace Modules\InsiderFramework\InsiderAcl;

use Modules\InsiderFramework\Core\RoutingSystem\RouteData;

/**
* Main class of Insider Framework default acl
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\InsiderAcl\InsiderAclMain
*/
class InsiderAclMain
{
    /**
    * Get user access level function
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\InsiderAcl\InsiderAclMain
    *
    * @param RouteData $routeObj Route object
    *
    * @return string Get the user access level
    */
    public static function getUserAccessLevelByRoute(RouteData $routeObj): string
    {
        $permissions = $routeObj->getPermissions();
        
        $server = \Modules\InsiderFramework\Core\KernelSpace::getVariable('SERVER', 'insiderFrameworkSystem');
        $consoleRequest = \Modules\InsiderFramework\Core\KernelSpace::getVariable('consoleRequest', 'insiderFrameworkSystem');
        $securitymodel = new \Modules\InsiderFramework\InsiderAcl\MockModel();

        if (isset($server['HTTP_USER_AGENT']) && $consoleRequest) {
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
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister('Error ! There are two users with the same access cookie!');
                }

                $arrayr = array();
    
                $arrayr['USERID'][] = $useridr['USERID'];

                $query = "select GROUPID from rel_users_groups where USERID = :userid";

                $bindarray = array(
                    'userid' => $useridr['USERID']
                );
                $usergroups = $securitymodel->select($query, $bindarray, true);

                $arrayr['USERGROUPS'] = [];

                foreach ($usergroups as $ugk => $ugv) {
                    $arrayr['USERGROUPS'][] = $ugv;
                }

                $renewAcces = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                    'renewAcces',
                    'insiderAcl'
                );

                if ($renewAcces) {
                    InsiderAclMain::renewAccess();
                }

                return ($arrayr);
            } else {
                return 'UNPRIVILEGEDUSER';
            }
        }
    }

    /**
    * Get user access level by rules property in permission annotation
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\InsiderAcl\InsiderAclMain
    *
    * @param string $rules Rules string for the route
    *
    * @return bool Return true if user is allowed to access the route
    */
    protected static function getUserAccessLevelByRules(string $rules): bool
    {
        // Checking route permissions
        $ut = $permissionsOfRoute['users']['type'];

        if ($permissionsOfRoute['users']['usersID'] !== "") {
            $uid = explode(",", $permissionsOfRoute['users']['usersID']);
            foreach ($uid as $k => $u) {
                $uid[$k] = intval($u);
            }
        } else {
            $uid = [];
        }

        if ($permissionNow !== "UNPRIVILEGEDUSER") {
            $pnowu = $permissionNow['USERID'];
        } else {
            $pnowu = $permissionNow;
        }

        $gt = $permissionsOfRoute['groups']['type'];

        if ($permissionsOfRoute['groups']['groupsID'] !== "") {
            $gid = explode(",", $permissionsOfRoute['groups']['groupsID']);
            foreach ($gid as $k => $g) {
                $gid[$k] = intval($g);
            }
        } else {
            $gid = [];
        }

        if ($permissionNow === null) {
            $permissionNow = "UNPRIVILEGEDUSER";
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                array(
                    'permissionNow' => $permissionNow
                ),
                'insiderFrameworkSystem'
            );
        }
        if ($permissionNow !== "UNPRIVILEGEDUSER") {
            if (!is_array($permissionNow)) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Expected array on return from permissions check function",
                    "app/sys"
                );
            }

            if (
                !isset($permissionNow['USERGROUPS']) ||
                !isset($permissionNow['USERID']) ||
                !is_array($permissionNow['USERGROUPS']) ||
                !is_array($permissionNow['USERID'])
            ) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Array on return of invalid permissions check function",
                    "app/sys"
                );
            }

            $pnowg = array();
            foreach ($permissionNow['USERGROUPS'] as $pkg => $pkv) {
                if (isset($pkv['GROUPID'])) {
                    $pnowg[] = intval($pkv['GROUPID']);
                } else {
                    $pnowg[] = intval($pkv);
                }
            }
        } else {
            $pnowg = $permissionNow;
        }

        $access = false;

        // Are there group restrictions on the route?
        if (count($gid) !== 0 && $gid !== "") {
            if ($pnowg != "UNPRIVILEGEDUSER") {
                if (is_array($pnowg)) {
                    $inc_g = array_intersect($gid, $pnowg);
                    switch ($gt) {
                        case "include":
                            if (count($inc_g) !== 0) {
                                $access = true;
                            } else {
                                $access = false;
                            }
                            break;

                        case "exclude":
                            if (count($inc_g) !== 0) {
                                $access = false;
                            } else {
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
                    $access = false;
                }
            } else {
                $access = false;
            }
        }

        // Are there user restrictions on the route?
        if (count($uid) !== 0) {
            if ($pnowu != "UNPRIVILEGEDUSER") {
                $inc_u = array_intersect($uid, $pnowu);

                switch ($ut) {
                    case "include":
                        if (count($inc_u) !== 0) {
                            $access = true;
                        } else {
                            $access = false;
                        }
                        break;

                    case "exclude":
                        if (count($inc_u) !== 0) {
                            $access = false;
                        } else {
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
                $access = false;
            }
        }
    }
    
    /**
    * Function that validates the user access level for a route
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\InsiderAcl\InsiderAclMain
    *
    * @param RouteData $routeObj  Object of the current route
    * @param mixed $currentUserAclPermission Current permissions for the current route
    *
    * @return bool Return true if user is allowed to access the route
    */
    public static function validateACLPermission(RouteData $routeObj): bool
    {
        $access = false;
        $permissionsOfRoute = $routeObj->getPermissions();
        $currentUserAclPermission = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'currentUserAclPermission',
            'routingSystem'
        );
        if ($currentUserAclPermission === null) {
            $currentUserAclPermission = "UNPRIVILEGEDUSER";
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                array(
                    'currentUserAclPermission' => $currentUserAclPermission
                ),
                'routingSystem'
            );
        }

        if (trim($permissionsOfRoute['rules']) == "") {
            $access = true;
            return $access;
        }

        $access = InsiderAclMain::getUserAccessLevelByRules($permissionsOfRoute['rules']);

        return $access;
    }
}
