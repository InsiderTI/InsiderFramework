<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Json;

final class JsonTest extends TestCase
{
  public function testShouldReadJsonAndReturnArray(): void
  { 
    $jsonContent = Json::getJSONDataFile(
        "Config" . DIRECTORY_SEPARATOR .
        "core.json"
    );

    $this->assertEquals('array', gettype($jsonContent));
  }

  public function testShouldReadJsonAndReturnObject(): void
  {
    $jsonContent = Json::getJSONDataFile(
        "Config" . DIRECTORY_SEPARATOR .
        "core.json",
        false
    );

    $this->assertEquals('object', gettype($jsonContent));
  }
}