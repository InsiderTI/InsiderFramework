<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Registry\Lib\Version;

final class VersionUnitTest extends TestCase
{
  public function testShouldValidateRightVersionSyntax(): void
  {
    $versionExample = "1.2.30-RC1";
    $validationData = Version::getVersionParts($versionExample);

    $isArray = is_array($validationData);
    $this->assertEquals(true, $isArray);
    $this->assertEquals(4, count($validationData));

    $this->assertEquals($validationData["part1"], 1);
    $this->assertEquals($validationData["part2"], 2);
    $this->assertEquals($validationData["part3"], 30);
    $this->assertEquals($validationData["part4"], 'RC1');
  }

  public function testShouldValidateWrongVersionSyntax(): void
  {
    $versionExample = "TEST_WRONG_SYNTAX";
    $validationData = Version::getVersionParts($versionExample);

    $isArray = is_array($validationData);
    $this->assertEquals(true, $isArray);
    $this->assertEquals(4, count($validationData));
    
    $this->assertEquals($validationData["part1"], 0);
    $this->assertEquals($validationData["part2"], 0);
    $this->assertEquals($validationData["part3"], 0);
    $this->assertEquals($validationData["part4"], NULL);
  }
}