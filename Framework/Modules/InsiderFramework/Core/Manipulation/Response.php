<?php

namespace Modules\InsiderFramework\Core\Manipulation;

trait Response
{
    /**
     * Return an JSON as an response of Response (method)
     *
     * @param mixed $data         Data to be returned
     * @param int   $responseCode Response code
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Response
     *
     * @return void
     */
    protected function responseJson($data, int $responseCode = null): void
    {
        if ($responseCode !== null) {
            http_response_code($responseCode);
        }
        header('Content-Type: application/json');
        echo Json::jsonEncodePrivateObject($data);
    }

    /**
     * Return an XML as an response of Response (method)
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Response
     *
     * @param mixed $data           Data to be returned
     * @param bool  $fixNumericKeys If true, set an preffix
     *                              to the numeric keys of XML
     * @param int   $responseCode   Response code
     *
     * @return void
     */
    protected function responseXml(
        $data,
        bool $fixNumericKeys = true,
        int $responseCode = null
    ): void {
        header('Content-type: text/xml');

        if ($fixNumericKeys === null) {
            $fixNumericKeys = false;
        }

        // Array
        if (is_array($data)) {
            $xmlObj = "";
            $xml = Xml::arrayToXML($data, $xmlObj, $fixNumericKeys);
            echo $xml->asXML();
        } elseif (is_string($data) || (is_int($data)) || is_float($data) || is_bool($data)) { // String
            $xml = new \SimpleXMLElement('<root/>');
            $xml->addChild("root", $data);
            echo $xml->asXML();
        } elseif (is_object($data)) { // Object
            $data = Aggregation::objectToArray($data);
            $this->responseXML($data);
        } elseif (is_resource($data)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'It is not possible to convert a resource into an XML',
                "app/sys"
            );
        } elseif ($data === null) {
            $xml = new \SimpleXMLElement('<root/>');
            $xml->addChild("root", "");
            echo $xml->asXML();
        } else { // Invalid/unknow type
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'It is not possible to convert a resource into an XML',
                "app/sys"
            );
        }
    }

    /**
     * Return an API response for the action of Response (method)
     *
     * @author Marcello Costa <marcello88costa@yahoo.com.br>
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Response
     *
     * @param mixed $data      Data to be returned
     * @param array $arrayArgs Additional arguments to the function
     *
     * @return void
     */
    protected function responseApi($data, array $arrayArgs = []): void
    {
        $debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        // If some error occours
        if (
            isset($debugBacktrace[0]) &&
            isset($debugBacktrace[0]["object"]) &&
            isset($debugBacktrace[0]["args"]) &&
            get_class($debugBacktrace[0]["object"]) === "Apps\Sys\Controllers\ErrorController"
        ) {
            if (DEBUG) {
                $data = array(
                    "error" => $debugBacktrace
                );
            } else {
                $data = $debugBacktrace[0]["args"][0];
            }
        }

        if (\Modules\InsiderFramework\Core\Validation\Aggregation::existAndIsNotEmpty($arrayArgs, 'responseFormat')) {
            $responseFormat = $arrayArgs['responseFormat'];
        } else {
            $responseFormat = KernelSpace::getVariable(
                'responseFormat',
                'insiderFrameworkSystem'
            );
            if ($responseFormat === "") {
                $responseFormat = DEFAULT_RESPONSE_FORMAT;
                KernelSpace::setVariable(
                    array(
                        'responseFormat' => $responseFormat
                    ),
                    'insiderFrameworkSystem'
                );
            }
        }

        switch ($responseFormat) {
            case "JSON":
                $responseCode = null;
                if (is_array($arrayArgs) && count($arrayArgs) > 0) {
                    $arrayArgs = array_change_key_case($arrayArgs, CASE_LOWER);
                    if (isset($arrayArgs['responsecode'])) {
                        $responseCode = intval($arrayArgs['responsecode']);
                    }
                }
                $this->responseJson($data, $responseCode);
                break;

            case "XML":
                $fixNumericKeys = true;
                $responseCode = null;
                if (is_array($arrayArgs) && count($arrayArgs) > 0) {
                    $arrayArgs = array_change_key_case($arrayArgs, CASE_LOWER);
                    if (isset($arrayArgs['responsecode'])) {
                        $responseCode = intval($arrayArgs['responseCode']);
                    }
                    if (isset($arrayArgs['fixnumerickeys'])) {
                        $fixNumericKeys = $arrayArgs['fixnumerickeys'];
                    }
                }
                $this->responseXML($data, $fixNumericKeys, $responseCode);
                break;

            default:
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'Invalid response type for API: %' . $responseFormat . '%',
                    "app/sys"
                );
                break;
        }
    }

    /**
    * Serve files with http
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Manipulation\Response
    *
    * @param string $originalFilePath File path of the file to be served
    * @param string $serveFileName    File name of the file to be served
    * @param string $contentType      Type of file
    * @param float  $downloadRate     Download rate for download. When
    *                                 specified 0, no limit will be applied
    *
    * @return void
    */
    public function serveFile(
        string $originalFilePath,
        string $serveFileName,
        string $contentType = "application/octet-stream",
        float $downloadRate = 0
    ): void {
        if (file_exists($originalFilePath) && is_file($originalFilePath)) {
            // Setting headers
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: filename=' . $originalFilePath);
            header('Cache-control: private');
            header('Content-Length: ' . filesize($originalFilePath));

            // Opening the file
            $file = fopen($originalFilePath, "r");
            print fread($file, filesize($originalFilePath));
            flush();
            fclose($file);
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister(
                'Error: The file ' . $originalFilePath .
                    ' does not exist!'
            );
        }
    }
}
