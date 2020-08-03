<?php

namespace Apps\Sys\Controllers;

/**
 * Class with package management functions
 *
 * @author Marcello Costa
 *
 * @package Apps\Sys\Controllers\PkgController
 *
 * @Route(path="/sys")
 */
class PkgController extends \Modules\InsiderFramework\Core\Controller
{
    /** @var string Mirror package directory */
    public static $mirrorDir = INSTALL_DIR . DIRECTORY_SEPARATOR . "mirror";
    
    /**
     * Function that returns information about an installed item
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\PkgController
     *
     * @Route (path="getinstallediteminfo")
     *
     * @param string $item          Item being fetched
     * @param string $authorization Authorization token
     * @param bool   $requestCall   Flag that determines whether the function is
     *                              being called via request or not
     *
     * @return string|void Item data
    */
    public function getInstalledItemInfo(
        string $item = null,
        string $authorization = null,
        bool $requestCall = false
    ): ?string {
        $POST = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'POST',
            'insiderFrameworkSystem'
        );

        if ($item === null || $authorization === null) {
            if (!is_array($POST)) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError('Wrong request body');
            }
            if (\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'item')) {
                $item = $POST['item'];
            }
            if (\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'authorization')) {
                $authorization = $POST['authorization'];
            }
            if ($item === null || $authorization === null) {
                if (!$requestCall) {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                        'Invalid arguments for sys/getinstallediteminfo route'
                    );
                } else {
                    return 'Invalid arguments for sys/getinstallediteminfo route';
                }
            }
        }

        $localAuthorization = \Modules\InsiderFramework\Core\Registry::getLocalAuthorization(REQUESTED_URL);

        if ($authorization === $localAuthorization) {
            $dataReturn = \Modules\InsiderFramework\Core\Registry::getItemInfo($item);

            if (!$requestCall) {
                return json_encode($dataReturn);
            } else {
                $this->responseJson($dataReturn);
            }
        } else {
            if ($authorization . "" !== "") {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister(
                    'Invalid Authorization Token: ' . $authorization
                );
            } else {
                if (!$requestCall) {
                    return json_encode('Received null Authorization Token');
                } else {
                    $this->responseJson('Received null Authorization Token');
                }
            }
        }
    }

    /**
     * Function that returns the formatted version of a package
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\PkgController
     *
     * @param array $dataInfoItem Data from a single package
     *
     * @return string|bool Package version
    */
    public function getVersionFromInfo(array $dataInfoItem)
    {
        if (
            isset($dataInfoItem['part1']) &&
            isset($dataInfoItem['part2']) &&
            isset($dataInfoItem['part3'])
        ) {
            return $dataInfoItem['part1'] . "." . $dataInfoItem['part2'] . "." . $dataInfoItem['part3'];
        }
        return false;
    }

    /**
     * Download a package in any of the configured mirrors
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\PkgController
     *
     * @param string $package Package name
     *
     * @return string Downloaded file path
    */
    public function downloadPackage(string $package): string
    {
        $console = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'console',
            'insiderFrameworkSystem'
        );

        // Array of packages found
        $foundPackages = [];

        // Searching for the version of the package installed locally (if installed)
        $localAuthorization = \Modules\InsiderFramework\Core\Registry::getLocalAuthorization(REQUESTED_URL);

        if ($localAuthorization === false) {
            $noAuthCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['NotAuth'];

            $msg = "Client Error - Cannot retrive local authorization for download package " . $localAuthorization;
            http_response_code($noAuthCode['responsecode']);
            error_log($msg);
            $this->responseJson($msg);
            die();
        }

        $localVersion = json_decode(
            $this->getInstalledItemInfo(
                $package,
                $localAuthorization,
                false
            )
        );

        // Variable that stores all mapped repositories
        $repoData = [];
        
        if (count(REMOTE_REPOSITORIES) === 0) {
            return "false";
        }
        
        $domain = "";

        foreach (REMOTE_REPOSITORIES as $repo) {
            if ($domain === "") {
                $parsedDomain = parse_url($repo['DOMAIN']);
                $domain = $parsedDomain['scheme'] . "://" . $parsedDomain['host'];
            }
            
            if (!isset($repoData[$domain])) {
                $post = array(
                    'item' => $package,
                    'authorization' => $repo['AUTHORIZATION']
                );

                $repoData[$domain] = $post;
            } else {
                $repoData[$domain] = $repo['AUTHORIZATION'];
            }
        }

        foreach ($repoData as $domain => $domainData) {
            $url = $domain . "/sys/existsinmirror";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'item' =>  $package,
                'authorization' => $localAuthorization
            ));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Framework_Internal_UserAgent');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $content = curl_exec($ch);

            if (curl_errno($ch)) {
                $msg = "Could not send request to server. ERROR: " . curl_error($ch);
                $console->br();
                $console->setTextColor('red');
                $console->write($msg);
                $console->br();
                continue;
            } else {
                $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($resultStatus == 200) {
                    if ($content !== null) {
                        $data = json_decode($content);

                        if (is_object($data) && (property_exists($data, 'version'))) {
                            $remoteVersion = $data->version;
                            $foundPackages[$domain] = array(
                                'version' => $remoteVersion,
                                'authorization' => $localAuthorization
                            );
                        } else {
                            $msg = "Request to server failed with content: '" . $content;
                            $console->br();
                            $console->setTextColor('red');
                            $console->write($msg);
                            $console->br();
                            continue;
                        }
                    }
                } else {
                    $addMessError = "";
                    if (curl_error($ch) !== "" && curl_error($ch) !== null) {
                        $addMessError = " Details: " . curl_error($ch);
                    } else {
                        $addMessError = " Details: " . $content;
                    }
                    $msg = "Request to server failed with status '" . $resultStatus . "'." . $addMessError;
                    $console->br();
                    $console->setTextColor('red');
                    $console->write($msg);
                    $console->br();
                    continue;
                }
            }
            curl_close($ch);
        }

        // When the search is finished, for each package found, check
        // on which server is the newest package
        $latestVersion = "";
        $latestServer = "";
        foreach ($foundPackages as $server => $data) {
            $version = $data['version'];

            $remoteVersion = \Modules\InsiderFramework\Core\Registry::getVersionParts($version);
            if ($remoteVersion === false) {
                $msg = "Wrong package version on remote server ($package): $version";
                $console->br();
                $console->setTextColor('red');
                $console->write($msg);
                $console->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }

            $remoteVersion = $this->getVersionFromInfo($remoteVersion);

            if (!is_object($localVersion) || (!property_exists($localVersion, 'version'))) {
                $msg = "Invalid local version registry: " . json_encode($localVersion);
                $console->br();
                $console->setTextColor('red');
                $console->write($msg);
                $console->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }

            // If it is 0.0.0 the package is not installed or if the server version is larger than the local version
            if ($localVersion->version === "0.0.0.") {
                if (version_compare($remoteVersion, $localVersion->version) > 0) {
                    $latestVersion = $remoteVersion;
                    $latestServer = $server;
                }
            } else {
                if (version_compare($remoteVersion, $localVersion->version) <= 0) {
                    $latestVersion = "up-to-date";
                } else {
                    $latestVersion = $remoteVersion;
                    $latestServer = $server;
                }
            }
        }

        if (filter_var($latestServer, FILTER_VALIDATE_URL) !== false) {
            if (!is_dir(PkgController::$mirrorDir)) {
                \Modules\InsiderFramework\Core\FileTree::createDirectory(PkgController::$mirrorDir, 777);
            }
            
            $fileDestPath = PkgController::$mirrorDir . DIRECTORY_SEPARATOR . $package . '-' . $remoteVersion . '.pkg';

            if (file_exists($fileDestPath)) {
                \Modules\InsiderFramework\Core\FileTree::deleteFile($fileDestPath);
            }

            set_time_limit(0);

            $fp = fopen($fileDestPath, 'w+');
            if (is_bool($fp)) {
                $msg = "Cannot open package file %$fileDestPath%";
                $console->br();
                $console->setTextColor('red');
                $console->write($i10nMsg);
                $console->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $post = array(
                'package' => $package,
                'authorization' => $foundPackages[$latestServer]['authorization'],
                'version' => $remoteVersion
            );

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
            curl_setopt($ch, CURLOPT_URL, $latestServer . "/sys/servepackage");
            curl_setopt($ch, CURLOPT_USERAGENT, 'Framework_Internal_UserAgent');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $pkgcontent = curl_exec($ch);
            
            if ($pkgcontent[0] == "<" || $pkgcontent[0] == "{") {
                $msg = "Error downloading package " . $pkgcontent;
                $console->br();
                $console->setTextColor('red');
                $console->write($msg);
                $console->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }

            $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fwrite($fp, $pkgcontent);
            fclose($fp);

            if (!file_exists($fileDestPath)) {
                $msg = "Cannot create package on mirror directory ";
                $console->br();
                $console->setTextColor('red');
                $console->write($msg);
                $console->br();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
            }
            
            return $fileDestPath;
        } else {
            if ($latestVersion === "up-to-date") {
                return $latestVersion;
            } else {
                return "false";
            }
        }
    }
    
    /**
     * Checks whether the package is available on the mirror.
     *
     * @todo Of course, this can have a cache on both sides,
     *       but for now it will be implemented without this functionality.
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\PkgController
     *
     * @Route(path="existsinmirror")
     *
     * @return void
     */
    public function existsInMirror(): void
    {
        $POST = \Modules\InsiderFramework\Core\KernelSpace::getVariable('POST', 'insiderFrameworkSystem');

        $error = false;
        if (
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'item')
        ) {
            $error = true;
        }
        if (
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'authorization')
        ) {
            $error = true;
        }
        
        if ($error) {
            $msg = 'Server Error - Invalid request parameters';
            $errorCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['CriticalError'];
            http_response_code($errorCode['responsecode']);
            error_log($msg);
            $this->responseJson($msg);
        }

        $item = $POST['item'];
        $authorization = $POST['authorization'];

        $domainForAuthorization = REQUESTED_URL;
        $localAuthorization = \Modules\InsiderFramework\Core\Registry::getLocalAuthorization($domainForAuthorization);

        if ($localAuthorization === false) {
            $noAuthCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['NotAuth'];

            $msg = "Server Error - Cannot retrive local authorization for " . $domainForAuthorization;
            http_response_code($noAuthCode['responsecode']);
            error_log($msg);
            $this->responseJson($msg);
            die();
        }

        if ($authorization !== $localAuthorization) {
            if ($localAuthorization . "" === "") {
                $msg = 'Server Error - Received null authorization token';
            } else {
                $msg = 'Server Error - Invalid authorization token: ' . $authorization;
            }
            $noAuthCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['NotAuth'];
            http_response_code($noAuthCode['responsecode']);
            error_log($msg);
            $this->responseJson($msg);
            die();
        }

        if (!is_dir(PkgController::$mirrorDir)) {
            \Modules\InsiderFramework\Core\FileTree::createDirectory(PkgController::$mirrorDir, 777);
            $msg = 'Server Error - Is is not a valid repository';
            $errorCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'routingActions',
                'RoutingSystem'
            )['CriticalError'];
            http_response_code($errorCode);
            error_log($msg);
            $this->responseJson($msg);
            die();
        }

        $fileName = PkgController::$mirrorDir . DIRECTORY_SEPARATOR . $item . "-*.pkg";

        $list = glob($fileName);

        // Whether the file (s) exists in the cache
        if (count($list) !== 0) {
            $latestPackage = "";
            foreach ($list as $file) {
                unset($fileVersion);
                $startIndexFile = strpos($file, "-");
                if (isset($file[$startIndexFile + 1])) {
                    $fileVersion = substr($file, $startIndexFile + 1);
                }
                if (isset($fileVersion) && $fileVersion !== "") {
                    $latestPackage = basename($fileVersion, '.pkg');
                }
            }

            $this->responseJson(array(
                "version" => $latestPackage
            ));
            return;
        }

        $msg = "Package '" . $item . "' not found";
        $notFoundCode = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'routingActions',
            'RoutingSystem'
        )['NotFound'];
        http_response_code($notFoundCode['responsecode']);
        $this->responseJson($msg);
        error_log($msg);
    }

    /**
     * Provides the download of a requested package via url
     *
     * @author Marcello Costa
     *
     * @package Apps\Sys\Controllers\PkgController
     *
     * @Route(path="servepackage")
     *
     * @return void
     */
    public function servepackage(): void
    {
        $POST = \Modules\InsiderFramework\Core\KernelSpace::getVariable('POST', 'insiderFrameworkSystem');
        
        if (
            !is_array($POST) ||
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'authorization') ||
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'version') ||
            !\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($POST, 'package')
        ) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister('Missing parameters on request');
        }

        $localAuthorization = \Modules\InsiderFramework\Core\Registry::getLocalAuthorization(REQUESTED_URL);
        $authorization = $POST['authorization'];
        $version = $POST['version'];
        $package = $POST['package'];

        if ($authorization !== $localAuthorization) {
            if ($localAuthorization . "" === "") {
                $msg = 'Server Error - Received null Authorization Token';
            } else {
                $msg = 'Server Error - Invalid Authorization Token: ' . $authorization;
            }
            \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister($msg);
        }

        if (!is_dir(PkgController::$mirrorDir)) {
            \Modules\InsiderFramework\Core\FileTree::createDirectory(PkgController::$mirrorDir, 777);
        }

        $pathOfPackage = PkgController::$mirrorDir . DIRECTORY_SEPARATOR . $package . "-" . $version . ".pkg";
        if (!file_exists($pathOfPackage)) {
            $i10nMsg = \Modules\InsiderFramework\Core\Manipulation\I10n::getTranslate(
                "The %" . $package . "% has not found in mirror",
                "app/sys"
            );
            $this->responseJson($i10nMsg, 404);
        } else {
            $this->serveFile($pathOfPackage, basename($pathOfPackage));
        }
    }
}
