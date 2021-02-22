<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Registry\Lib\ModuleInfo;

final class ModuleInfoUnitTest extends TestCase
{
  public function testShouldSetAndGetPackageFromModuleInfo(): void
  {
    $moduleName = 'TestPkg';
    $moduleInfo = new ModuleInfo();
    $moduleInfo->setPackage($moduleName);
    $testPkg = $moduleInfo->getPackage($moduleName);

    $this->assertEquals($moduleName, $testPkg);
  }

  public function testShouldSetAndGetSectionFromModuleInfo(): void
  {
    $sectionName = 'TestSection';
    $moduleInfo = new ModuleInfo();
    $moduleInfo->setSection($sectionName);
    $testSection = $moduleInfo->getSection($sectionName);
    $this->assertEquals($sectionName, $testSection);
  }

  public function testShouldSetAndGetVersionFromModuleInfo(): void
  {
    $version = '1.2.3-RC1';
    $moduleInfo = new ModuleInfo();
    $moduleInfo->setVersion($version);
    $testVersion = $moduleInfo->getVersion($version);
    $this->assertEquals($version, $testVersion);
  }

  public function testShouldSetAndGetAuthorsFromModuleInfo(): void
  {
    $authors = 'johndoe@test.com';
    $moduleInfo = new ModuleInfo();
    $moduleInfo->setAuthors($authors);
    $testAuthors = $moduleInfo->getAuthors($authors);
    $this->assertEquals($authors, $testAuthors);
  }

  public function testShouldSetAndGetProvidesFromModuleInfo(): void
  {    
    $modulesToProvide = array(
        "module1" => '1.0.0',
        "module2" => '1.2.3-RC1',
    );

    $moduleInfo = new ModuleInfo();
    foreach($modulesToProvide as $moduleName => $moduleVersion){
        $moduleInfo->addProvides($moduleName, $moduleVersion);
    }

    $testProvides = $moduleInfo->getProvides();
    $this->assertEquals($modulesToProvide, $testProvides);
  }

  public function testShouldSetAndGetDependsFromModuleInfo(): void
  {    
    $modulesToDepend = array(
        "module1" => '1.0.0',
        "module2" => '1.2.3-RC1',
    );

    $moduleInfo = new ModuleInfo();
    foreach($modulesToDepend as $moduleName => $moduleVersion){
        $moduleInfo->addDepends($moduleName, $moduleVersion);
    }

    $testDepends = $moduleInfo->getDepends();
    $this->assertEquals($modulesToDepend, $testDepends);
  }

  public function testShouldSetAndGetRecommendsFromModuleInfo(): void
  {    
    $modulesToRecommend = array(
        "module1" => '1.0.0',
        "module2" => '1.2.3-RC1',
    );

    $moduleInfo = new ModuleInfo();
    foreach($modulesToRecommend as $moduleName => $moduleVersion){
        $moduleInfo->addRecommends($moduleName, $moduleVersion);
    }

    $testRecommends = $moduleInfo->getRecommends();
    $this->assertEquals($modulesToRecommend, $testRecommends);
  }

  public function testShouldSetAndGetDescription(): void
  {
    $description = 'Description example for module';
    $moduleInfo = new ModuleInfo();
    $moduleInfo->setDescription($description);
    $testDescription = $moduleInfo->getDescription($description);
    $this->assertEquals($description, $testDescription);
  }
}