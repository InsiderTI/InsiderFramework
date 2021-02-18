<?php
namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;
class RequestedUrl {
  /**
   * Domain used to request
   *
   * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\RequestedUrl
   */
  public static function load(): void {   
      $proto = 'http';
      if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])) {
          $proto = $_SERVER["HTTP_X_FORWARDED_PROTO"];
      }
      if (!isset($_SERVER['SHELL']) && isset($_SERVER['HTTP_HOST'])) {
          define('REQUESTED_URL', $proto . "://" . $_SERVER['HTTP_HOST']);
      } else {
          define('REQUESTED_URL', $proto . "://localhost");
      }
  }
}