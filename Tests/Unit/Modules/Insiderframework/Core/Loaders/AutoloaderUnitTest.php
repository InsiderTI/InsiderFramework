<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Loaders\Autoloader;

final class AutoloaderUnitTest extends TestCase
{
  public function testShouldAutoLoaderAlreadyBeenLoaded(): void {
    $loadedFunctions = isset(spl_autoload_functions()[1]);
    $this->assertEquals(true, $loadedFunctions);
  }

  public function testShouldReturnPathOfAFile(): void
  {
    $soughtitem = "Example";
    $expectedPath = str_replace(
      "\\",
      DIRECTORY_SEPARATOR,
      INSTALL_DIR . DIRECTORY_SEPARATOR .
      $soughtitem . ".php"
    );

    $path = Autoloader::getFrameworkClassFilePath($soughtitem);
    $this->assertEquals($expectedPath, $path);
  }

  public function testShouldLoadFakeClass(): void {
    $returnOfMethod = \Modules\Insiderframework\Tests\Foo::bar();
    $this->assertEquals(null, $returnOfMethod);
  }
}