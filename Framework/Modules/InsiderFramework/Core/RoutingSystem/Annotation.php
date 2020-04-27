<?php

namespace Modules\InsiderFramework\Core\RoutingSystem;

/**
 * Classe de manipulação de annotations
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation
 */
class Annotation
{
    /**
     * Função que converte as annotations em array de dados
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation
     *
     * @param string $id       Identificação da classe
     * @param string $comments Comentários a serem analisados
     *
     * @return array Dados extraídos das annotations
     */
    public static function getAnnotationsData($id, $comments): array
    {
        $routingSettings = \Modules\InsiderFramework\Core\KernelSpace::getVariable('routingSettings', 'RoutingSystem');

        // Comentários sem tratamento de cas
        $commentsWithCase = explode("\n", $comments);

        // Colocando tudo em minúsculas e dividindo comentários em linhas
        if ($routingSettings['routeCaseSensitive']) {
            $comments = explode("\n", $comments);
        } else {
            $comments = explode("\n", strtolower($comments));
        }

        // Array de propriedades encontradas
        $annotationData = [];
        $annotationData[$id] = [];

        // Para cada linha dos comentários
        foreach ($comments as $commentK => $comment) {
            // Buscando declaração de annotation
            preg_match_all(read::$patternArgs, $comment, $annotationMatches, PREG_SET_ORDER, 0);

            // Se encontrar uma declaração
            if (is_array($annotationMatches) && count($annotationMatches) > 0) {
                $declaration = strtolower(str_replace(" ", "", $annotationMatches[0]['declaration']));
                switch ($declaration) {
                    case 'author':
                        $index = 0;
                        if (isset($annotationData[$id]['author'])) {
                            $index = count($annotationData[$id]['author']);
                        }
                        $annotationData[$id]['author'][$index] = implode(' ', array_slice($declaration, 1));
                        break;
                    case 'route':
                        if (isset($annotationData[$id]['route'])) {
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                "The %" . "@Route" . "% statement is duplicated in the " .
                                "class declaration in the controller %" . $id . "%",
                                "app/sys"
                            );
                        }

                        // Se o roteamento está como case sensitive
                        if ($routingSettings['routeCaseSensitive']) {
                            // Buscando declaração de annotation com case sensitive
                            preg_match_all(
                                read::$patternArgs,
                                $commentsWithCase[$commentK],
                                $annotationMatchesWithCase,
                                PREG_SET_ORDER,
                                0
                            );

                            // Buscando os argumentos no comentário
                            preg_match_all(
                                read::$betweenCommasPattern,
                                $annotationMatchesWithCase[0]['args'],
                                $argsCase,
                                PREG_SET_ORDER,
                                0
                            );
                        }

                        // Buscando os argumentos no comentário
                        preg_match_all(
                            read::$betweenCommasPattern,
                            $annotationMatches[0]['args'],
                            $args,
                            PREG_SET_ORDER,
                            0
                        );

                        // Para cada argumento da rota
                        foreach ($args as $argK => $arg) {
                            $argument = trim(strtolower($arg['Argument']));
                            if (isset($annotationData[$id]['route'][$argument])) {
                                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                    "The %" . "@" . $argument . "% argument is " .
                                    "duplicated in statement " . $declaration . " " .
                                    "in the controller %" . $id . "%",
                                    "app/sys"
                                );
                            }

                            // Se o roteamento está como case sensitive
                            if ($routingSettings['routeCaseSensitive']) {
                                $annotationData[$id]['route'][$argument] = trim($argsCase[$argK]['Data']);
                            } else {
                                $annotationData[$id]['route'][$argument] = trim($arg['Data']);
                            }
                        }
                        break;
                    case 'permission':
                        if (isset($annotationData[$id]['permission'])) {
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                "The %" . "@Permission" . "% statement is " .
                                "duplicated in the class declaration in the " .
                                "controller %" . $id . "%",
                                "app/sys"
                            );
                        }

                        // Buscando declaração de annotation com case sensitive
                        preg_match_all(
                            read::$patternArgs,
                            $commentsWithCase[$commentK],
                            $annotationMatchesWithCase,
                            PREG_SET_ORDER,
                            0
                        );

                        // Buscando os argumentos no comentário (com e sem case)
                        preg_match_all(
                            read::$betweenCommasPattern,
                            $annotationMatchesWithCase[0]['args'],
                            $argsWithCase,
                            PREG_SET_ORDER,
                            0
                        );
                        preg_match_all(
                            read::$betweenCommasPattern,
                            $annotationMatches[0]['args'],
                            $args,
                            PREG_SET_ORDER,
                            0
                        );

                        // Para cada argumento da rota
                        foreach ($args as $argK => $arg) {
                            $argument = trim(strtolower($arg['Argument']));
                            if (isset($annotationData[$id]['permission'][$argument])) {
                                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                    "The %" . "@" . $argument . "% argument is duplicated in statement " .
                                    $declaration . " in the controller %" . $id . "%",
                                    "app/sys"
                                );
                            }

                            switch ($argument) {
                                case 'rules':
                                    $annotationData[$id]['permission'][$argument] = trim($argsWithCase[$argK]['Data']);
                                    break;
                                default:
                                    $annotationData[$id]['permission'][$argument] = trim($arg['Data']);
                                    break;
                            }
                        }
                        break;
                    case 'param':
                        if (!isset($annotationData[$id]['param'])) {
                            $annotationData[$id]['param'] = [];
                        }

                        // Se o roteamento está como case sensitive
                        if ($routingSettings['routeCaseSensitive']) {
                            // Buscando declaração de annotation com case sensitive
                            preg_match_all(
                                read::$patternArgs,
                                $commentsWithCase[$commentK],
                                $annotationMatchesWithCase,
                                PREG_SET_ORDER,
                                0
                            );

                            $paramRoute = explode(',', $annotationMatchesWithCase[0]['args']);
                        } else {
                            $paramRoute = explode(',', $annotationMatches[0]['args']);
                        }

                        foreach ($paramRoute as $pR) {
                            // Buscando declaração de annotation
                            preg_match_all(
                                read::$betweenCommasPattern,
                                $pR,
                                $pRMatches,
                                PREG_SET_ORDER,
                                0
                            );

                            if (count($pRMatches) > 0) {
                                $annotationData[$id]['param'][$pRMatches[0]['Argument']] = $pRMatches[0]['Data'];
                            }
                        }
                        break;

                    case "verbs":
                        if (isset($annotationData[$id]['verbs'])) {
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                "The %" . "@Verbs" . "% statement is duplicated " .
                                "in the class declaration in the controller %" . $id . "%",
                                "app/sys"
                            );
                        }
                        $verbsRoute = explode(',', $annotationMatches[0]['args']);
                        $annotationData[$id]['verbs'] = $verbsRoute;
                        break;

                    case "cache":
                        if (isset($annotationData[$id]['cache'])) {
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                "The %" . "@Cache" . "% statement is duplicated " .
                                "in the class declaration in the controller " .
                                "%" . $id . "%",
                                "app/sys"
                            );
                        }
                        $verbsRoute = explode(',', $annotationMatches[0]['args']);
                        $annotationData[$id]['cache'] = $verbsRoute;
                        break;

                    case "domains":
                        if (isset($annotationData[$id]['domains'])) {
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                "The %" . "@Domains" . "% statement is duplicated " .
                                "in the class declaration in the controller " .
                                "%" . $id . "%",
                                "app/sys"
                            );
                        }
                        $domainsRoute = explode(',', $annotationMatches[0]['args']);
                        $annotationData[$id]['domains'] = $domainsRoute;
                        break;

                    case "responseformat":
                        if (isset($annotationData[$id]['responseformat'])) {
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                "The %" . "@Responseformat" . "% statement is duplicated " .
                                "in the class declaration in the controller %" . $id . "%",
                                "app/sys"
                            );
                        }
                        $annotationData[$id]['responseformat'] = $annotationMatches[0]['args'];
                        break;
                }
            }
        }

        return $annotationData;
    }
}
