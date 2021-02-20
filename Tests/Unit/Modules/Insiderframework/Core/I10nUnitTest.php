<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\I10n;

final class I10nUnitTest extends TestCase
{
  public function testShouldGetTranslateForAString(): void
  {
    // TODO
    // I10n::getTranslate('test');
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  public function testShouldGetAndSetCurrentLinguas(): void
  {
    $newLinguas = 'ptbr';
    $oldLinguas = LINGUAS;
    I10n::setCurrentLinguas($newLinguas);
    $currentValue = I10n::getCurrentLinguas();
    $this->assertEquals($newLinguas, $currentValue);
    I10n::setCurrentLinguas($oldLinguas);
  }

  public function testShouldLoadI10nFileToKernelSpace(): void {
    // TODO
    // $i10n = \Modules\Insiderframework\Core\KernelSpace::getVariable('i10n', 'insiderFrameworkSystem');
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }
}