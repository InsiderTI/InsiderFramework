<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Registry;

final class RegistryUnitTest extends TestCase
{
  public function testShouldListAllInstalledModules(): void
  {
    $listOfModules = Registry::getListOfInstalledModules();
    $this->assertEquals('array', gettype($listOfModules));
    $this->assertGreaterThan(0, count($listOfModules));
  }
}