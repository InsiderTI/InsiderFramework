<?php

/**
  Classe de permissões do módulo de roteamento
 */
// Namespace insiderRoutingSystem

namespace Modules\insiderRoutingSystem;

/**
 * Classe de manipulação de permissões
 *
 * @author Marcello Costa
 */
class Permission {
    /**
        Função padrão (native) do framework para checagem de segurança. Mais detalhes
        sobre como utilizar esta função na documentação.

        @author Marcello Costa
  
        @package Controllers\sys\Security_Controller
          
        @return  mixed   Array de dados recuperados do banco ou string 'UNPRIVILEGEDUSER'
    */
    public static function getNativeAccessLevel() {
        global $kernelspace;
        $server = $kernelspace->getVariable('SERVER', 'insiderFrameworkSystem');
        $consoleRequest = $kernelspace->getVariable('consoleRequest', 'insiderFrameworkSystem');
        
        // Model para manipulação do banco
        $securitymodel=new \KeyClass\Model();

        // Se for uma requisição especial
        if (isset($server['HTTP_USER_AGENT']) && $consoleRequest) {
            // Se a URL contiver o cookie idsession do usuário
            if (isset($server['QUERY_STRING']) && (strpos($server['QUERY_STRING'],'cookieframeidsession') !== false)) {
                // Recuperando o valor do cookie
                preg_match("/cookieframeidsession=([^&]*)/", $server['QUERY_STRING'], $matches);

                // Definindo o valor do cookie manualmente
                \KeyClass\Security::setCookie("idsession", $matches[1]);
                $cookie=$matches[1];
            }
        }
        else {
            // Tratando o cookie de sessão
            $cookie=\KeyClass\Security::getCookie('idsession');
        }

        // Se o cookie não existir
        if ($cookie === null) {
            // Sem permissões para qualquer operação
            return 'UNPRIVILEGEDUSER';
        }

        // Cookie existe no navegador !
        else {
            // Buscando o cookie na lista de usuários conectados
            $query = "select USERID from login_registry where KEYCOOKIE = :keycookie";
            $bindarray = array(
                'keycookie' => $cookie
            );

            $useridr=$securitymodel->select($query, $bindarray, true);

            // Se o cookie está no banco
            if (!(empty($useridr))) {
                // Se existir mais de um user com o mesmo cookie
                if (!(isset($useridr['USERID']))) {
                    \KeyClass\Error::errorRegister('Error ! There are two users with the same access cookie!');
                }

                // Array de retorno de informações
                $arrayr=array();

                // Armazenando ID do usuário no array de retorno
                $arrayr['USERID'][]=$useridr['USERID'];

                // Consultando os grupos aos quais o usuário pertence
                $query = "select GROUPID from rel_users_groups where USERID = :userid";

                $bindarray = array(
                    'userid' => $useridr['USERID']
                );
                $usergroups=$securitymodel->select($query, $bindarray, true);

                // Inicializando USERGROUPS
                $arrayr['USERGROUPS']=[];

                // Armazenando os grupos que o usuário pertence
                foreach ($usergroups as $ugk => $ugv) {
                    $arrayr['USERGROUPS'][]=$ugv;
                }

                // Se o segundo nível de acesso existir
                if (isset($sec_result)) {
                    // Unindo os dois arrays
                    $arrayr=\KeyClass\Code::arrayMergeRecursiveDistinct($arrayr, $sec_result);
                }

                // Habilite esta linha para ativar a renovação automática da
                // sessão/cookie do usuário em cada requisição
                // renewAccess();

                // Retornando os grupos do usuário e o ID do usuário
                return($arrayr);
            }

            // Se o cookie não está no banco
            else {
                // Sem permissões para qualquer operação
                return 'UNPRIVILEGEDUSER';
            }
        }
    }
    
    /**
        Função que verifica a permissão do usuário atual utilizando
        a lógica do ACL "native"

        @author Marcello Costa

        @package Core

        @param  \Modules\insiderRoutingSystem\routeData  $routeObj    Objeto de rota
        @param  array  $permissionNow    Permissões atuais
        @param  bool   $access    Booleano que indica se o usuário tem acesso ou não
                                  à rota atual

        @return  void  Without return
    */
    public static function validateNativeACLPermission(
            \Modules\insiderRoutingSystem\routeData $routeObj, 
            array $permissionNow, 
            bool &$access) : void {

        global $kernelspace;
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
        if ($permissionNow !== "UNPRIVILEGEDUSER") {
            $pnowu = $permissionNow['USERID'];
        } else {
            $pnowu = $permissionNow;
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
        if ($permissionNow === null) {
            $permissionNow = "UNPRIVILEGEDUSER";
            $kernelspace->setVariable(array('permissionNow' => $permissionNow), 'insiderFrameworkSystem');
        }
        if ($permissionNow !== "UNPRIVILEGEDUSER") {
            if (!is_array($permissionNow)) {
                \KeyClass\Error::i10nErrorRegister("Expected array on return from permissions check function", 'pack/sys');
            }

            if (!isset($permissionNow['USERGROUPS']) || !isset($permissionNow['USERID']) || !is_array($permissionNow['USERGROUPS']) || !is_array($permissionNow['USERID'])) {
                \KeyClass\Error::i10nErrorRegister("Array on return of invalid permissions check function", 'pack/sys');
            }

            // Para cada item de grupo encontrado
            // constrói o array de grupos
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
                            }
                            // Se o usuário não estiver listado
                            else {
                                // Não autorizado
                                $access = false;
                            }
                        break;

                        case "exclude":
                            // Se o usuário esta em algum desses grupos
                            if (count($inc_g) !== 0) {
                                // Autorizado
                                $access = false;
                            }

                            // Se o usuário não estiver listado
                            else {
                                // Não autorizado
                                $access = true;
                            }
                        break;

                        default:
                            primaryError("Permissions error on the route " . $route);
                        break;
                    }
                } else {
                    // Não autorizado
                    $access = false;
                }
            }

            // O usuário está deslogado
            else {
                // Não autorizado
                $access = false;
            }
        }

        // Existem restrições de usuários na rota ?
        if (count($uid) !== 0) {
            // O usuário está logado
            if ($pnowu != "UNPRIVILEGEDUSER") {
                // Intercessão de usuários na regra
                $inc_u = array_intersect($uid, $pnowu);;
                
                // As permissões de grupo são exclusão ou inclusão ?
                switch ($ut) {
                    case "include":
                        // Se o usuário é algum destes listados
                        if (count($inc_u) !== 0) {
                            // Autorizado
                            $access = true;
                        }

                        // Se o usuário não estiver listado
                        else {
                            // Não autorizado
                            $access = false;
                        }
                    break;

                    case "exclude":
                        // Se o usuário é algum destes listados
                        if (count($inc_u) !== 0) {
                            // Não autorizado
                            $access = false;
                        }

                        // Se o usuário não estiver listado
                        else {
                            // Autorizado
                            $access = true;
                        }
                    break;

                    default:
                        primaryError("Permissions error on the route " . $route);
                    break;
                }
            }

            // O usuário está deslogado
            else {
                // Não autorizado
                $access = false;
            }
        }
    }
}
