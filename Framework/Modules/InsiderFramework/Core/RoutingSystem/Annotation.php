<?php

namespace Modules\InsiderFramework\Core\RoutingSystem;

/**
 * Annotation handling class
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation
 */
class Annotation
{
    /**
     * Function that converts annotations to a data array
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation
     *
     * @param string $id       Class identification
     * @param string $comments Comments to be reviewed
     *
     * @return array Data extracted from annotations
     */
    public static function getAnnotationsData($id, $comments): array
    {
        $routingSettings = \Modules\InsiderFramework\Core\KernelSpace::getVariable('routingSettings', 'RoutingSystem');
        $commentsWithCase = explode("\n", $comments);

        if ($routingSettings['routeCaseSensitive']) {
            $comments = explode("\n", $comments);
        } else {
            $comments = explode("\n", strtolower($comments));
        }

        $annotationData = [];
        $annotationData[$id] = [];

        foreach ($comments as $commentK => $comment) {
            preg_match_all(read::$patternArgs, $comment, $annotationMatches, PREG_SET_ORDER, 0);

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

                        if ($routingSettings['routeCaseSensitive']) {
                            preg_match_all(
                                read::$patternArgs,
                                $commentsWithCase[$commentK],
                                $annotationMatchesWithCase,
                                PREG_SET_ORDER,
                                0
                            );

                            preg_match_all(
                                read::$betweenCommasPattern,
                                $annotationMatchesWithCase[0]['args'],
                                $argsCase,
                                PREG_SET_ORDER,
                                0
                            );
                        }

                        preg_match_all(
                            read::$betweenCommasPattern,
                            $annotationMatches[0]['args'],
                            $args,
                            PREG_SET_ORDER,
                            0
                        );

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

                        preg_match_all(
                            read::$patternArgs,
                            $commentsWithCase[$commentK],
                            $annotationMatchesWithCase,
                            PREG_SET_ORDER,
                            0
                        );

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

                        if ($routingSettings['routeCaseSensitive']) {
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
