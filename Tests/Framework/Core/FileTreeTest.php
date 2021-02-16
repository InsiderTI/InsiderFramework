<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\FileTree;

final class FileTreeTest extends TestCase
{
  public function testShouldReadFileContent(): void
  {
    $test = FileTree::fileReadContent();
    var_dump($test);
    die();
  }
}