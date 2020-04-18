<?php

namespace Modules\InsiderFramework\Sagacious\Lib;

/**
 * Classe que manipula o código de views e templates
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsCodeManipulation
 */
class SgsCodeManipulation
{
    /**
     * Removes PHP comments of a line
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsCodeManipulation
     *
     * @param string $newline      Line to be processed
     * @param string $commentfound Flag for control if an comment has been found
     *
     * @return void
     */
    public static function removePHPComments(
        string &$newline,
        string &$commentfound
    ): void {
        // If a comment as already been found
        if ($commentfound == true) {
            // While a closing tag was not been found
            // the line will be empty
            if (strpos($newline, '*/') === false) {
                $newline = null;
            } else {
                // It is necessary check where the comment is.
                // If is located on the end of line
                if (strpos((trim($newline)), '*/') == strlen(trim($newline))) {
                    // Então a linha fica em branco
                    $newline = null;
                } else {
                    // From the position that starts to the end of line,
                    // removing everything
                    $cposStart = 0;
                    $cposEnd = strpos($newline, '*/');
                    $replaceStr = \Modules\InsiderFramework\Core\Manipulation\Text::extractString(
                        $newline,
                        $cposStart,
                        $cposEnd + 2
                    );
                    $newline = str_replace($replaceStr, "", $newline);

                    if (trim($newline) == "*/") {
                        $newline = null;
                    }

                    $commentfound = false;
                }
            }
        } else {
            // If a comments has been founded in just one line
            if (strpos($newline, '//') !== false) {
                // From the position that starts to the end, removes everything
                $cposStart = strpos($newline, '/*');
                $cposEnd = strlen($newline);
                $replaceStr = \Modules\InsiderFramework\Core\Manipulation\Text::extractString(
                    $newline,
                    $cposStart,
                    $cposEnd
                );
                $newline = str_replace($replaceStr, "", $newline);
            } else {
                // If a comment it's not founded in just one line, makes the normal
                // logic
                // Verifing if a comment exists
                if (strpos($newline, '/*') !== false) {
                    $commentfound = true;

                    // If a comment starts on the begining of line
                    // and if exists an end to him on the end of line
                    if (
                        (strpos((trim($newline)), '/*') == 0)
                        && (strpos((trim($newline)), '*}') === strlen(trim($newline)))
                    ) {
                        // So the line will be null
                        $newline = null;
                    } else {
                        // If the end of comment not exists yet
                        if (strpos($newline, '*/') === false) {
                            $commentfound = true;
                            // From the position that starts to the end of line,
                            // removing everything
                            $cposStart = strpos($newline, '/*');
                            $cposEnd = strlen($newline);
                            $replaceStr = \Modules\InsiderFramework\Core\Manipulation\Text::extractString(
                                $newline,
                                $cposStart,
                                $cposEnd
                            );
                            $newline = str_replace($replaceStr, "", $newline);
                        } elseif (strpos((trim($newline)), '*/') !== strlen(trim($newline))) {
                            // From the position that starts to the end of line,
                            // removing everything
                            $cposStart = strpos($newline, '/*');
                            $cposEnd = strpos($newline, '*/');
                            $replaceStr = \Modules\InsiderFramework\Core\Manipulation\Text::extractString(
                                $newline,
                                $cposStart,
                                $cposEnd + 2
                            );
                            $newline = str_replace($replaceStr, "", $newline);
                            $commentfound = false;
                        } else {
                            // If the comment ends on this line
                            if (strpos($newline, '*/') !== false) {
                                // Removes the comment
                                $cposStart = strpos($newline, '/*');
                                $cposEnd = strpos($newline, '*/');
                                $replaceStr = \Modules\InsiderFramework\Core\Manipulation\Text::extractString(
                                    $newline,
                                    $cposStart,
                                    $cposEnd + 2
                                );
                                $newline = str_replace($replaceStr, "", $newline);
                                $commentfound = false;
                            } else {
                                // From the position that starts to the end of line,
                                // removing everything
                                $cposStart = strpos($newline, '/*');
                                $cposEnd = strlen($newline);
                                $replaceStr = \Modules\InsiderFramework\Core\Manipulation\Text::extractString(
                                    $newline,
                                    $cposStart,
                                    $cposEnd
                                );
                                $newline = str_replace($replaceStr, "", $newline);
                            }
                        }
                    }
                }
            }
        }
    }
}
