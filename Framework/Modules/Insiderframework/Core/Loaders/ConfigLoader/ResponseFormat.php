<?php
namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;

class ResponseFormat {
  /**
   * ResponseFormat config loader
   *
   * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\ResponseFormat
   */
  public static function load(array $coreData): void {
    if (!isset($coreData['DEFAULT_RESPONSE_FORMAT'])) {
        \Modules\Insiderframework\Core\Error::primaryError(
            "The following information was not found in the configuration: 'DEFAULT_RESPONSE_FORMAT'"
        );
    }

    /*
     * Application default response format
     *
     * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\ResponseFormat
     */
    define('DEFAULT_RESPONSE_FORMAT', $coreData['DEFAULT_RESPONSE_FORMAT']);
  }
}