<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\FileTree;

final class FileTreeTest extends TestCase
{
  public function testShouldReadFileContent(): void
  {
    $filepath = '';
    $returnstring = true;
    $delaytry = 0.15;
    $maxToleranceLoops = null;

    $fileContent = FileTree::fileReadContent(
      'Tests' . DIRECTORY_SEPARATOR .
      'phpunit.xml'
    ) ? true : false;

    $this->assertEquals(true, $fileContent);
  }
}