<?php

namespace Modules\InsiderFramework\Sagacious\Lib;

/**
 * Class of handling elements on the page
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
 */
class SgsPage
{
  /**
   * Dynamically updates the page's CSS when,
   * for example, a new component is created and it must
   * modify the page css
   *
   * @author Marcello Costa
   *
   * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
   *
   * @param string $css CSS code (with style tag) to be placed on the page
   *
   * @return void
   */
    public static function updateCssOfPage(string $css): void
    {
        $cssExploded = explode("\n", $css);

        $newcss = "";

        $countCss = count($cssExploded);

        foreach ($cssExploded as $i => $cssline) {
            if ($i < $countCss - 1) {
                $newcss .= $cssline . "\n";
            } else {
                $newcss .= $cssline;
            }
        }

        $injectedCss = \Modules\InsiderFramework\Core\KernelSpace::getVariable('injectedCss', 'sagacious');
        $injectedCss = $injectedCss . $newcss;
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'injectedCss' => $injectedCss
            ),
            'sagacious'
        );
    }

    /**
     * Dynamically updates the page's JS when,
     * for example, a new component is created and it must
     * modify the page js
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
     *
     * @param string $js JS code to be added (with <script> tag);
     *
     * @return void
     */
    public static function updateJsOfPage(string $js): void
    {
        $injectedScripts = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'injectedScripts',
            'sagacious'
        );
        $injectedScripts = $injectedScripts . $js;
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
              'injectedScripts' => $injectedScripts
            ),
            'sagacious'
        );
    }

    /**
     * Dynamically updates the HTML of the page when,
     * for example, a new component is created and it must
     * modify the page's html
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
     *
     * @param string $html HTML code to be added; HTML code to be added
     *
     * @return void
     */
    public static function updateHtmlOfPage(string $html): void
    {
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array(
          'injectedHtml' => \Modules\InsiderFramework\Core\KernelSpace::getVariable(
              'injectedHtml',
              'sagacious'
          ) . $html
        ), 'sagacious');
    }

    /**
      * Function that minifies JS code
      *
      * @author Marcello Costa
      *
      * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
      *
      * @param string $js JS code to be minified
      *
      * @return string Minified JS
    */
    public static function jsMinify(string $js): string
    {
        $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
        $js = preg_replace($pattern, '', $js);
        $js = str_replace(array("\n", "\r"), '', $js);
        $js = preg_replace('!\s+!', ' ', $js);

        return $js;
    }

    /**
     * Function that minifies a CSS code
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
     *
     * @param string $css CSS code to be minified
     *
     * @return string Minified CSS
    */
    public static function cssMinify(string $css): string
    {
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        $css = str_replace(': ', ':', $css);
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);

        return $css;
    }

    /**
     * Function that translates a string based on the ID
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
     *
     * @param string       $app  Name of the app where the translation is located
     * @param string|array $id   String or Array of string to be translated
     * @param string       $lang Language into which the string will be translated
     *
     * @return string Minified CSS
   */
    public static function translateText(string $app, $id, string $lang = LINGUAS): string
    {
        $pathLang = INSTALL_DIR . DIRECTORY_SEPARATOR . "Apps" . DIRECTORY_SEPARATOR .
        $app . DIRECTORY_SEPARATOR . "I10n" . DIRECTORY_SEPARATOR . $lang;

        if (!is_dir($pathLang)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Error trying to translate string %" . $id . "%" .
                " - Directory not found: %" . $pathLang . "%",
                "app/sys"
            );
        }

        $content = [];

        foreach (\Modules\InsiderFramework\Core\FileTree::dirTree($pathLang) as $file) {
            $contentFile = \Modules\InsiderFramework\Core\Json::getJSONDataFile($file);
            if ($contentFile !== false && $contentFile !== null) {
                $content = array_merge($contentFile, $content);
            }
        }

        if (count($content) > 0) {
            $content = array_change_key_case($content, CASE_LOWER);
        }

        if (!isset($content[$id])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Error trying to translate string %" . $id . "%" .
                " - String translation ID not found",
                "app/sys"
            );
        }

        $contentToReturn = $content[$id];
        if (is_array($contentToReturn)) {
            $contentToReturn = implode('', $contentToReturn);
        }
        return $contentToReturn;
    }
}
