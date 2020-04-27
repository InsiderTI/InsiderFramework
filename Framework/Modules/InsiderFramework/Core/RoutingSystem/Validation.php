<?php

namespace Modules\InsiderFramework\Core\RoutingSystem;

/**
 * Classe de validação de rotas/urls
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Validation
 *
 * @author Marcello Costa
 */
class Validation
{
    /**
     * Trata uma url e retorna os dados da rota e action
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Validation
     *
     * @return array Route and action data
     */
    public static function getRouteAndActionDataFromUrl($url): array
    {
        $server = \Modules\InsiderFramework\Core\KernelSpace::getVariable('SERVER', 'insiderFrameworkSystem');

        // Dados da rota
        $ajaxRequest = false;

        // Rota atual
        $routeNow = "";

        // Ação atual
        $actionNow = "";

        // Verificando se é uma requisição Ajax
        if (
            !empty($server['HTTP_X_REQUESTED_WITH']) &&
            strtolower($server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            $ajaxRequest = true;
        }

        // Recuperando a rota e seus argumentos adicionais
        $url = Validation::getOnlyRouteCall($url);

        // Separando controller, action e o resto
        // Se a URL for vazia, a rota é a home ("/")
        if ($url == "") {
            // Definindo a rota: home
            $routeNow = "/";
            $actionNow = "";
        } else {
            // Verificando se um JSON foi enviado pela URL
            $JSONpattern = '
            /
            \{              # { character
                (?:         # non-capturing group
                    [^{}]   # anything that is not a { or }
                    |       # OR
                    (?R)    # recurses the entire pattern
                )*          # previous group zero or more times
            \}              # } character
            /x
            ';

            // Array de marcadores de JSON
            $jsonMarkers = [];

            // Removendo os JSONs da URL e colocando marcadores
            // nas posições
            preg_match_all($JSONpattern, $url, $matches);
            if (count($matches) > 0) {
                foreach ($matches as $matchesJSON) {
                    foreach ($matchesJSON as $Json) {
                        $jsonMarker = "JSONMARKER_" . \uniqid();
                        $jsonMarkers[$jsonMarker] = $Json;
                        $url = str_replace($Json, $jsonMarker, $url);
                    }
                }
            }

            // Separando a url de acordo com as barras
            $urlexploded = explode('/', $url);

            // Esta é a rota
            $route = $urlexploded[1];

            // Contando parâmetros da URL
            $countUrl = count($urlexploded);

            // Se existem outra partes enviadas
            if ($countUrl > 2) {
                // Removendo o primeiro elemento (que é sempre nulo)
                array_shift($urlexploded);

                // Contando parâmetros da URL novamente
                $countUrl = count($urlexploded);
            }

            // Reinserindo o JSON nas partes da URL
            for ($i = 0; $i < $countUrl; $i++) {
                if (array_key_exists($urlexploded[$i], $jsonMarkers)) {
                    $urlexploded[$i] = $jsonMarkers[$urlexploded[$i]];
                }
            }

            // Definindo a rota atual
            $routeNow = $urlexploded[0];

            // Definindo a action atual
            array_shift($urlexploded);
            $actionNow = implode("/", $urlexploded);
        }


        $routingSettings = \Modules\InsiderFramework\Core\KernelSpace::getVariable('routingSettings', 'RoutingSystem');

        // Se o roteamento não está como case sensitive
        if (!$routingSettings['routeCaseSensitive']) {
            return array(
                'originalUrlRequested' => strtolower($url),
                'routeNow' => strtolower($routeNow),
                'actionNow' => strtolower($actionNow),
                'ajaxRequest' => $ajaxRequest
            );
        } else {
            return array(
                'originalUrlRequested' => $url,
                'routeNow' => $routeNow,
                'actionNow' => $actionNow,
                'ajaxRequest' => $ajaxRequest
            );
        }
    }

    /**
     * Função que captura a action e os dados adicionais da URL requisitada
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Validation
     *
     * @param string $url URL a ser analisada
     *
     * @return string Action e dados adicionais da URL
     */
    protected static function getOnlyRouteCall($url): string
    {
        $parsedUrl = parse_url($url);

        if ($parsedUrl['path'][0] !== '/') {
            $parsedUrl['path'] = "/" . $parsedUrl['path'];
        }

        return $parsedUrl['path'];
    }
}
