<?php

namespace Modules\InsiderFramework\Core\RoutingSystem\Annotation;

use Modules\InsiderFramework\Core\RoutingSystem\Read;

/**
 * Annotation handling class
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\ReadAnnotation
 */
class ReadAnnotation
{
    /**
     * Function that converts annotations to a data array
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\ReadAnnotation
     *
     * @param string $id       Identification of annotation
     * @param string $comments Comments to be reviewed
     *
     * @return array Data extracted from annotations
     */
    public static function getAnnotationsData(string $id, string $comments): array
    {
        $routingSettings = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'routingSettings',
            'RoutingSystem'
        );

        if ($routingSettings['routeCaseSensitive']) {
            $comments = explode("\n", $comments);
        } else {
            $comments = explode("\n", strtolower($comments));
        }

        $annotationsData = [];
        $annotationsData[$id] = [];

        foreach ($comments as $commentK => $commentLine) {
            preg_match_all(Read::$declarationPattern, $commentLine, $declarationMatches, PREG_SET_ORDER, 0);

            if (is_array($declarationMatches) && count($declarationMatches) > 0) {
                $declaration = ucwords(str_replace(" ", "", $declarationMatches[0]['declaration']));
                $annotationClass = "\\Modules\\InsiderFramework\\Core\\RoutingSystem\\Annotation\\Type\\$declaration";

                if (!class_exists($annotationClass)) {
                    continue;
                }

                $annotationClass::handler(
                    $id,
                    $annotationsData,
                    $commentLine
                );
            }
        }

        return $annotationsData;
    }
}
