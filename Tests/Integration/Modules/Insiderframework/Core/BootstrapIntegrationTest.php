<?php

use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\KernelSpace;

final class BootstrapIntegrationTest extends TestCase
{
    public function testShouldHaveAppAndInstallDirConstants(): void {
      $this->assertEquals(true, defined('INSTALL_DIR'));
      $this->assertEquals(true, defined('APP_ROOT'));
    }

    public function testShouldFrameworkBeBootstraped(): void
    {
      $frameworkLoadStatus = KernelSpace::getVariable('FRAMEWORK_LOAD_STATUS', 'insiderFrameworkSystem');
      $this->assertEquals('LOADED', $frameworkLoadStatus);
    }
  }