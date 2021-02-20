<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\KernelSpace;

final class KernelSpaceUnitTest extends TestCase
{
  public function testShouldSetAndGetVariableFromKernelSpace(): void
  {
    KernelSpace::setVariable(array('test' => 123), 'testContext');
    $tmpVar = KernelSpace::getVariable('test', 'testContext');
    $this->assertEquals(123, $tmpVar);
  }
}