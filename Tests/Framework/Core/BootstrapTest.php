<?php

use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\KernelSpace;

final class BootstrapTest extends TestCase
{
    public function testShouldBeBootstrapFramework(): void
    {
      $frameworkLoadStatus = KernelSpace::getVariable('FRAMEWORK_LOAD_STATUS', 'insiderFrameworkSystem');
      $this->assertEquals('LOADED', $frameworkLoadStatus);
    }
  }