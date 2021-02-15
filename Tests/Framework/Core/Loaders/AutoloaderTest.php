<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Loaders\AutoLoader;

final class AutoloaderTest extends TestCase
{
    public function testShouldBeLoadAutoloader(): void
    {
        $loadedFunctions = isset(spl_autoload_functions()[1]);
        $this->assertEquals(true, $loadedFunctions);
    }

    public function testShouldLoadFakeClass(): void {
        $returnOfMethod = \Modules\Insiderframework\Tests\Foo::bar();
        $this->assertEquals(null, $returnOfMethod);
    }
}
