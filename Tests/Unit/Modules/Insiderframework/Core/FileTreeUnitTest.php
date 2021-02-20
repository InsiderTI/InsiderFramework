<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\FileTree;

final class FileTreeUnitTest extends TestCase
{
  public function testShouldReadFileContent(): void
  {
    $fileContent = FileTree::fileReadContent(
      'Tests' . DIRECTORY_SEPARATOR .
      'phpunit.xml'
    ) ? true : false;

    $this->assertEquals(true, $fileContent);
  }
}