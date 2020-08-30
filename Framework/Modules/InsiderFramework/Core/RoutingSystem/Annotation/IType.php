<?php

namespace Modules\InsiderFramework\Core\RoutingSystem\Annotation;

interface IType
{
    public static function handler(
        string $id,
        array &$annotationsData,
        string $commentLine
    ): void;
}
