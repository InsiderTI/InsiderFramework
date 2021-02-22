<?php

namespace Modules\Insiderframework\Core\Registry\Lib;

class Version {
    /**
     * Returns each version part
     *
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\Registry\Lib\Version
     *
     * @param string $version Version
     *
     * @return array Parts of the version
     */
    public static function getVersionParts(string $version): array
    {
        $regexVersion = "/(?P<part1>([0-9]*))" .
                        "(?P<separator1>.)(?P<part2>([0-9]*))" .
                        "(?P<separator2>.)(?P<part3>([0-9]*))" .
                        "((?P<separator3>-)(?P<part4>.*))?/";

        $versionData = [];
        preg_match_all($regexVersion, $version, $versionMatches, PREG_SET_ORDER);

        if (count($versionMatches) == 0) {
            return false;
        }

        $part1 = intval($versionMatches[0]['part1']);
        $part2 = intval($versionMatches[0]['part2']);
        $part3 = intval($versionMatches[0]['part3']);
        $part4 = null;
        if (isset($versionMatches[0]['part4'])) {
            $part4 = $versionMatches[0]['part4'];
        }

        return array(
            'part1' => $part1,
            'part2' => $part2,
            'part3' => $part3,
            'part4' => $part4,
        );
    }
}