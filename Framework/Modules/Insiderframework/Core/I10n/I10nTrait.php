<?php

namespace Modules\Insiderframework\Core\I10n;

/**
 * Methods responsible for handle globalization
 *
 * @author Marcello Costa
 *
 * @package Modules\Insiderframework\Core\Manipulation\I10n
 */
trait I10nTrait
{
    /**
    * Get current linguas configuration
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Manipulation\I10n\I10nTrait
    *
    * @return string Current linguas configuration
    */
    public static function getCurrentLinguas(): string
    {
        $currentLinguas = \Modules\Insiderframework\Core\KernelSpace::getVariable(
            'linguas',
            'insiderFrameworkSystem'
        );

        return $currentLinguas;
    }

    /**
    * Set current linguas configuration
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Manipulation\I10n\I10nTrait
    *
    * @param string $linguas Current linguas to be setted
    *
    * @return void
    */
    public static function setCurrentLinguas(string $linguas): void
    {
        \Modules\Insiderframework\Core\KernelSpace::setVariable(
            array('linguas' => $linguas),
            'insiderFrameworkSystem'
        );
    }

    /**
     * Gets a translation for a string
     *
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\Manipulation\I10n\I10nTrait
     *
     * @param string $stringToTranslate String to be translated
     * @param string $domain            Domain which the translation belongs
     * @param string $linguas           Languague which the string will be translated
     *
     * @return string Translated string
     */
    public static function getTranslate(string $stringToTranslate, string $domain, string $linguas = null): string
    {
        if ($linguas === null) {
            $linguas = \Modules\Insiderframework\Core\I10n::getCurrentLinguas();
        }

        $i10n = \Modules\Insiderframework\Core\KernelSpace::getVariable(
            'i10n',
            'insiderFrameworkSystem'
        );

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
     * @TODO 
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\Manipulation\I10n\I10nTrait
     *
     * @param string $domain   Domain which the translation belongs
     * @param string $filePath Path to the translation file
     *
     * @return void
     */
    // TODO: Registry must be implemented first
    public static function loadi10nFile(string $domain, string $filePath): void
    {
        $i10n = \Modules\Insiderframework\Core\KernelSpace::getVariable('i10n', 'insiderFrameworkSystem');

        if (!file_exists($filePath)) {
            \Modules\Insiderframework\Core\Error::errorRegister(
                "Cannot load file %" . $filePath . "%",
                "LOG"
            );
        }

        $language = basename(dirname(strtolower($filePath)));
        $i10nData = \Modules\Insiderframework\Core\Json::getJSONDataFile($filePath);

        if (!$i10nData) {
            \Modules\Insiderframework\Core\Error::errorRegister(
                "Cannot load file contents %" . $filePath . "%",
                "LOG"
            );
        }

        if (!isset($i10n[$domain])) {
            $i10n[$domain] = [];
        }
        if (!isset($i10n[$domain][$language])) {
            $i10n[$domain][$language] = [];
        }

        $i10n[$domain][$language] = array_merge($i10n[$domain][$language], $i10nData);

        \Modules\Insiderframework\Core\KernelSpace::setVariable(array('i10n' => $i10n), 'insiderFrameworkSystem');
    }
}
