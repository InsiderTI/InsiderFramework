<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Loaders\ConfigLoader;

final class ConfigLoaderTest extends TestCase
{
    public function testShouldGetConfigFileContent(): void
    {
        $configPath = "core";
        $configContent = ConfigLoader::getConfigFileData($configPath) ? true : false;

        $this->assertEquals(true, $configContent);
    }
}