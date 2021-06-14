<?php

use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Registry;

final class RegistryIntegrationTest extends TestCase
{
    public function testShouldGetInfoFromAllModules(): void {
      $moduleInfos = Registry::getAllModulesInfo();
      $this->assertEquals('array', gettype($moduleInfos));
      $this->assertGreaterThan(0, count($moduleInfos));
    }

    public function testShouldThrownExceptionForInvalidModuleName(): void {
        $result = null;
        try{
            Registry::getModuleInfo('NonExistentModule');
        } catch(\Exception $err) {
            $result = $err->getMessage();
        }

        $this->assertNotNull($result);
    }
}