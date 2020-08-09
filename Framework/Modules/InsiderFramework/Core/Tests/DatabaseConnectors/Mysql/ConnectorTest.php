<?php

namespace Modules\InsiderFramework\Core\Tests\DatabaseConnectors\Mysql;

/**
* Class responsible for testing of the connector for MySql
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\DatabaseConnectors\Mysql\ConnectorTest
*/
class ConnectorTest extends \PHPUnit\Framework\TestCase
{
    /**
    * Connection method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\DatabaseConnectors\Mysql\ConnectorTest
    *
    * @return void
    */
    public function testConnect(): void
    {
        $exampleModel = $this->getMockForAbstractClass('\\Modules\\InsiderFramework\\Core\\Model');

        $mySqlConnector = new \Modules\InsiderFramework\Core\DatabaseConnectors\Mysql\Connector();
        $mySqlConnector->connect($exampleModel);

        $connectionStatus = $exampleModel->checkConnection();
        
        $this->assertEquals(true, $connectionStatus);
    }
}
