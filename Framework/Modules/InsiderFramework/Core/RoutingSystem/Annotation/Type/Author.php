<?php

namespace Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type;

use Modules\InsiderFramework\Core\Registry;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\IType;
use Modules\InsiderFramework\Core\RoutingSystem\Read;

;

/**
 * Author annotation handler class
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Author
 */
class Author implements IType
{
    public static $authorRegex = '/@(?P<declaration>([^ ]+)) (?P<name>([^<]+))((<(?P<mail>.*))[>]+?)?/';

    /**
    * Handler for author declaration
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Author
    *
    * @param string $id                 Identification of annotation
    * @param array  $annotationsData    Current mapped annotation data
    * @param string $commentLine        Current comment line
    *
    * @return void
    */
    public static function handler(
        string $id,
        array &$annotationsData,
        string $commentLine
    ): void {
        preg_match_all(Author::$authorRegex, $commentLine, $declarationMatches, PREG_SET_ORDER, 0);

        if (!is_array($declarationMatches) || count($declarationMatches) === 0) {
            return;
        }

        $index = 0;
        if (isset($annotationsData[$id]['author'])) {
            $index = count($annotation);
        } else {
            $annotationsData[$id]['author'] = [];
        }

        $name = '';
        if (isset($declarationMatches['name'])) {
            $name = $declarationMatches['name'];
        }

        $mail = '';
        if (isset($declarationMatches['mail'])) {
            $mail = $declarationMatches['mail'];
        }

        $annotationsData[$id]['author'][$index] = array(
            'name' => $name,
            'mail' => $mail
        );
    }
}
