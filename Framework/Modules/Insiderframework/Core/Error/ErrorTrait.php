<?php

namespace Modules\Insiderframework\Core\Error;

/**
 * Class that contains functions for handling errors
 *
 * @package Modules\Insiderframework\Core\Error\ErrorTrait
 *
 * @author Marcello Costa
 */
trait ErrorTrait
{
    public static $frameworkErrorTypes = [
      'CRITICAL',
      'XML_PRE_CONDITION_FAILED',
      'JSON_PRE_CONDITION_FAILED',
      'ATTACK_DETECTED',
      'LOG',
      'WARNING'
    ];

    /**
     * Function that allows you to trigger an error directly to the user
     * and stop the execution of php script
     *
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\Error\ErrorTrait
     *
     * @param string $msg          Error message
     * @param int    $errorCode    Error code
     * @param string $outputFormat HTML or JSON
     *
     * @return array Returns the result
     */
    public static function primaryError(string $msg, int $errorCode = 500, string $outputFormat = 'JSON'): array
    {
      http_response_code($errorCode);

      $output = $msg;
      if (strtoupper($outputFormat) === 'JSON') {
          $msgToUser = [];
          $msgToUser['error'] = $msg;
          $output = json_encode($msgToUser);
      }

      throw new \RuntimeException($output);
    }

    /**
     * Register/Show errors
     *
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\Error\ErrorTrait
     *
     * @param string $message             Error message
     * @param string $frameworkErrorType  Framework error type
     * @param int    $responseCode        Response code of the error
     *
     * @return void|string Returns the uniqid of the error if it's of type LOG
     */
    public static function errorRegister(string $message, string $frameworkErrorType = "CRITICAL", int $responseCode = null): ?string
    {
      // TODO
      ErrorTrait::primaryError('Error Register must be implemented');
    }
  }