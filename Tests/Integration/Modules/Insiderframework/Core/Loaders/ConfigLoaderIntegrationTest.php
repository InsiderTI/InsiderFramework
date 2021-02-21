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
        $this->assertEquals(true, defined('ENCODE'));
        $this->assertEquals(true, defined('ENCRYPT_KEY'));

        $this->assertEquals(true, defined('MAILBOX_SMTP_PORT'));
        $this->assertEquals(true, defined('MAILBOX_SMTP_SECURE'));
        $this->assertEquals(true, defined('MAILBOX_SMTP_AUTH'));
        $this->assertEquals(true, defined('MAILBOX_PASS'));
        $this->assertEquals(true, defined('MAILBOX'));
    }
}