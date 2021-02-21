<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Loaders\ConfigLoader;

final class ConfigLoaderIntegrationTest extends TestCase
{
    public function testShouldFrameworkMainConstantsBeLoaded(): void
    {
        // From ConfigLoader::initializeConfigVariablesFromConfigFiles();
        $this->assertEquals(true, defined('REQUESTED_URL'));
        $this->assertEquals(true, defined('LOCAL_REPOSITORIES'));
        $this->assertEquals(true, defined('REMOTE_REPOSITORIES'));
        $this->assertEquals(true, defined('LINGUAS'));
    }
}