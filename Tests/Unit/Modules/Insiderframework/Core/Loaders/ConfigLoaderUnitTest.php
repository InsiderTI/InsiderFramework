<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Loaders\ConfigLoader;

final class ConfigLoaderUnitTest extends TestCase
{
    public function testShouldGetConfigFileContent(): void
    {
        $configPath = "core";
        $configContent = ConfigLoader::getConfigFileData($configPath) ? true : false;

        $this->assertEquals(true, $configContent);
    }

    public function testShouldGetFrameworkConfigVariablesFromConfigFiles(): void
    {
        $configVariables = ConfigLoader::getFrameworkConfigVariablesFromConfigFiles();
        $valid = false;

        if (is_array($configVariables) && !empty($configVariables)) {
            $valid = true;
        }

        $this->assertEquals(true, $valid);
    }
}