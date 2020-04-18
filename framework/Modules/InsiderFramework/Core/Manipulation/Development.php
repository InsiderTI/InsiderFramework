<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods for Development
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Manipulation\Development
 */
trait Development
{
    /**
     * Returns the namespace, the classes and the methods inside an php file
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Development
     *
     * @param string $filepath Path of PHP file
     *
     * @return array Namespace and classes inside php file
     */
    public static function fileGetPHPTokens(string $filepath): array
    {
        $phpCode = \Modules\InsiderFramework\Core\FileTree::fileReadContent($filepath);
        $namespace = "";
        $classes = array();
        $methods = array();
        \Modules\InsiderFramework\Core\Manipulation::getTokens($phpCode, $namespace, $classes, $methods);

        if ($namespace === "") {
            throw new \Exception(
                "No namespace was found in the file <b>" . $filepath . "</b> !"
            );
        }

        return (array(
            'namespace' => $namespace,
            'classes' => $classes,
            'methods' => $methods
        ));
    }

    /**
     * Returns the namespace and clases inside an php code
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Development
     *
     * @param string $phpCode   PHP code that contains classes
     * @param string $namespace External variable which will be receive the name of
     *                          namespace of the code
     * @param array  $classes   External variable which will be receive the names of
     *                          classes of code
     * @param array  $methods   External variable which will be receive the names of
     *                          methods of code
     *
     * @return void
     */
    public static function getTokens(string $phpCode, string &$namespace, array &$classes, array &$methods): void
    {
        // Yes, I know: the functions "token_get_all" can be used without this
        // complicated logic below. But, to avoid problems, the comments of php
        // code are removed before.

        // Flag for comments found
        $commentfound = false;

        // Code without comments
        $nocomments_code = array();
        $phpCode = explode("\n", $phpCode);

        foreach ($phpCode as $line_num => $line) {
            // Variable to the new line formatted
            $newline = $line;

            // Removing comments
            \Modules\InsiderFramework\Core\Manipulation::removePHPComments($newline, $commentfound);

            // Insert the code without comments on array
            if ($newline != null) {
                $nocomments_code[] = $newline;
            }
        }

        unset($phpCode);
        $codestring = "";
        foreach ($nocomments_code as $l) {
            $codestring .= "\r\n" . $l;
        }

        // Turn everything in token
        $tokens = token_get_all($codestring);

        // Getting the namespace
        $namespace = \Modules\InsiderFramework\Core\Manipulation\Development::getNamespace($tokens);

        // Getting the functions
        $methods = \Modules\InsiderFramework\Core\Manipulation\Development::getFunctions($tokens);

        // Getting the classes
        $classes = \Modules\InsiderFramework\Core\Manipulation\Development::getClasses($tokens);
    }

    /**
     * Returns the namespace inside an array of tokens
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Development
     *
     * @param array $tokens Tokens of a php code
     *
     * @return string Name of namespace
     */
    public static function getNamespace(array $tokens): string
    {
        $count = count($tokens);
        $nfound = false;
        $namespace = "";

        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i][0] == T_NAMESPACE) {
                $nfound = $i;
            }

            if ($nfound !== false) {
                $i2 = $i;
                while ($tokens[$i2] !== ";") {
                    $namespace .= $tokens[$i2][1];

                    $i2++;
                    // Loop break
                    if ($i2 === 1000) {
                        return false;
                    }
                }
                $nfound = false;

                $namespace = str_replace("namespace ", "", $namespace);

                return ($namespace);
            }
        }
    }

    /**
     * Returns the classes inside an array of tokens
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Development
     *
     * @param array $tokens Tokens of a php code
     *
     * @return array Founded classes in tokens
     */
    public static function getClasses(array $tokens): array
    {
        $classes = array();
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if (
                $tokens[$i - 2][0] == T_CLASS &&
                $tokens[$i - 1][0] == T_WHITESPACE &&
                $tokens[$i][0] == T_STRING
            ) {
                $className = $tokens[$i][1];
                $classes[] = $className;
            }
        }
        return $classes;
    }

    /**
     * Returns the functions inside an array of tokens
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Development
     *
     * @param array $tokens Tokens of a php code
     *
     * @return array Founded functions in tokens
     */
    public static function getFunctions(array $tokens): array
    {
        $count = count($tokens);
        $methods = array();

        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i][0] == T_FUNCTION) {
                $methods[] = $tokens[$i + 2][1];
            }
        }

        return $methods;
    }

    /**
     * Function that can be used for debug the code. The result is similar to
     * "var_dump" function.
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Development
     *
     * @param mixed $var Variable to be displayed
     *
     * @return void
     */
    public static function printDump($var): void
    {
        echo '<pre dir="ltr" class="xdebug-var-dump">';
        print_r($var) . "</pre>";
    }
}
