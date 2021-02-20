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
      if (strtoupper($outputFormat) === 'JSON') {
          $msgToUser = [];
          $msgToUser['error'] = $msg;
          $output = json_encode($msgToUser);
      } else {
          $output = $msg;
      }

      throw new \RuntimeException($output);
    }
  }