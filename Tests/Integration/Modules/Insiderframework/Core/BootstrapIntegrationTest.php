<?php

use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\KernelSpace;

final class BootstrapIntegrationTest extends TestCase
{
    public function testShouldFrameworkBeBootstraped(): void
    {
      $frameworkLoadStatus = KernelSpace::getVariable('FRAMEWORK_LOAD_STATUS', 'insiderFrameworkSystem');
      $this->assertEquals('LOADED', $frameworkLoadStatus);
    }
  }