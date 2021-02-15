<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
require('Modules/Insiderframework/Core/Loaders/Autoloader.php');
use \Modules\InsiderFramework\Core\Loaders\AutoLoader;

final class AutoloaderTest extends TestCase
{
    public function testShouldLoadAutoLoaderFunction(): void
    {
        Autoloader::initializeAutoLoader();
        $loadedFunctions = isset(spl_autoload_functions()[1]);
        $this->assertEquals(true, $loadedFunctions);
    }
}
