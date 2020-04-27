<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods responsible for handle JWT
 *
 * @author   Marcello Costa <marcello88costa@yahoo.com.br>
 * @link     https://www.insiderframework.com/documentation/keyclass#jwt
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package  Modules\InsiderFramework\Core\Manipulation\JWT
 */
trait Jwt
{
    /**
     * Função para ler um token JWT
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\JWT
     *
     * @return array Array de dados do token
    */
    public static function getJwtTokenFromAuthorizationHeader(): array
    {
        $token = null;
        $headers = apache_request_headers();
        $data = [];

        if (isset($headers['Authorization'])) {
            $matches = array();
            preg_match('/Bearer (.*)/', $headers['Authorization'], $matches);
            if (isset($matches[1])) {
                $token = $matches[1];
            }
        } else {
            $data['error'] = "No Bearer found";
        }
        
        return $data;
    }
}
