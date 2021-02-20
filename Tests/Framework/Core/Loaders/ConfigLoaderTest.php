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

    public function testShouldGetFrameworkConfigVariablesFromConfigFiles(): void
    {
        $configVariables = ConfigLoader::getFrameworkConfigVariablesFromConfigFiles();
        $valid = false;

        if (is_array($configVariables) && !empty($configVariables)) {
            $valid = true;
        }

        $this->assertEquals(true, $valid);
    }

    public function testShouldLoadFrameworkConstants(): void
    {
        // ConfigLoader::initializeConfigVariablesFromConfigFiles();
        $this->assertEquals(true, defined('REQUESTED_URL'));
        $this->assertEquals(true, defined('LOCAL_REPOSITORIES'));
        $this->assertEquals(true, defined('REMOTE_REPOSITORIES'));
        $this->assertEquals(true, defined('LINGUAS'));
    }
}