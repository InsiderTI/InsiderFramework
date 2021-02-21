<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Loaders\ConfigLoader;
use \Modules\Insiderframework\Core\KernelSpace;

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
        $this->assertEquals(true, defined('ERROR_MAIL_SENDING_POLICY'));

        $this->assertEquals(true, defined('DEFAULT_RESPONSE_FORMAT'));
        $this->assertEquals(true, defined('ACL_DEFAULT_ENGINE'));

        $this->assertEquals(true, defined('MAX_TOLERANCE_LOOPS'));

        $this->assertEquals(true, defined('DEBUG'));
        $this->assertEquals(true, defined('DEBUG_BAR'));

        $injectedCss = KernelSpace::getVariable('injectedCss', 'sagacious');

        $this->assertEquals(true, $injectedCss !== null);

        $loadAVG = \Modules\Insiderframework\Core\KernelSpace::getVariable(
            'loadAVG',
            'insiderFrameworkSystem'
        );
        $this->assertEquals(true, $loadAVG !== null);
    }
}