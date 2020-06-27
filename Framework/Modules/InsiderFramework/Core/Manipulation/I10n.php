<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods responsible for handle globalization
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Manipulation\I10n
 */
trait I10n
{
    /**
     * Gets a translation for a string
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\I10n
     *
     * @param string $stringToTranslate String to be translated
     * @param string $domain            Domain which the translation belongs
     * @param string $linguas           Languague which the string will be translated
     *
     * @return string Translated string
     */
    public static function getTranslate(string $stringToTranslate, string $domain, string $linguas = LINGUAS): string
    {
        $i10n = \Modules\InsiderFramework\Core\KernelSpace::getVariable('i10n', 'insiderFrameworkSystem');

        if ($i10n === null) {
            return "";
        }

        // If there is no translation for the message
        if (!isset($i10n[$domain]) || !isset($i10n[$domain][$linguas])) {
            return str_replace("%", "", $stringToTranslate);
        }

        $regex = '/(?P<matches>(%.*?%))/';

        $tmpString = preg_replace_callback($regex, function ($gMT) use ($linguas, $domain, $i10n, $stringToTranslate) {
            return 'REPLACE_STRING';
        }, $stringToTranslate);

        $shortest = 99999;
        foreach ($i10n[$domain][$linguas] as $original => $translate) {
            $lev = levenshtein($tmpString, $original);

            if ($lev <= $shortest || $shortest < 0) {
                // Set the closest match (with shortest distance)
                $closest  = $translate;
                $shortest = $lev;
            }
        }

        preg_replace_callback(
            $regex,
            function ($gMT) use (
                &$closest,
                $regex,
                $linguas,
                $domain,
                $i10n,
                $stringToTranslate
            ) {
                $matchesString = [];
                preg_match_all($regex, $stringToTranslate, $matchesString, PREG_SET_ORDER);

                $matchesClosest = [];
                preg_match_all($regex, $closest, $matchesClosest, PREG_SET_ORDER);

            // For each "match" (string to be replaced)
                foreach ($matchesString as $mS) {
                    $newString = str_replace('%', '', $mS['matches']);

                    $closest = str_replace($matchesClosest[0]['matches'], $newString, $closest);
                }
            },
            $stringToTranslate
        );

        return $closest;
    }

    /**
     * Function that load a translation file
     *
     * @author Marcello Costa <marcello88costa@yahoo.com.br>
     *
     * @package Modules\InsiderFramework\Core\Manipulation\I10n
     *
     * @param string $domain   Domain which the translation belongs
     * @param string $filePath Path to the translation file
     *
     * @return void
     */
    public static function loadi10nFile(string $domain, string $filePath): void
    {
        $i10n = \Modules\InsiderFramework\Core\KernelSpace::getVariable('i10n', 'insiderFrameworkSystem');

        // If file did not exists
        if (!file_exists($filePath)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister(
                "Cannot load file %" . $filePath . "%",
                "LOG"
            );
        }

        // Language of the translation
        $language = basename(dirname(strtolower($filePath)));

        // Reading the translation file
        $i10nData = \Modules\InsiderFramework\Core\Json::getJSONDataFile($filePath);

        if (!$i10nData) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister(
                "Cannot load file contents %" . $filePath . "%",
                "LOG"
            );
        } else {
            // Placing the data inside the translation domain
            if (!isset($i10n[$domain])) {
                $i10n[$domain] = [];
            }
            if (!isset($i10n[$domain][$language])) {
                $i10n[$domain][$language] = [];
            }

            $i10n[$domain][$language] = array_merge($i10n[$domain][$language], $i10nData);
        }

        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('i10n' => $i10n), 'insiderFrameworkSystem');
    }
}
