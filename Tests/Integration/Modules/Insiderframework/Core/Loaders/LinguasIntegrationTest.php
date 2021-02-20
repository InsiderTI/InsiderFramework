<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\I10n;

final class LinguasIntegrationTest extends TestCase
{
  public function testShouldLinguasBeSetted(): void
  {
    $currentLinguas = I10n::getCurrentLinguas();
    $this->assertEquals(true, gettype($currentLinguas) === 'string' && !empty($currentLinguas));

    $linguasInKernelSpace = \Modules\Insiderframework\Core\KernelSpace::getVariable(
      'linguas',
      'insiderFrameworkSystem'
    );

    $this->assertEquals(LINGUAS, $linguasInKernelSpace);
    $this->assertEquals(LINGUAS, $currentLinguas);   
  }

  public function testShouldLoadI10nFromApps(): void {
    // TODO: Variable i10n in kernelSpace must be filled with strings for all apps
    // $i10n = \Modules\Insiderframework\Core\KernelSpace::getVariable('i10n', 'insiderFrameworkSystem');
    // var_dump($i10n);
    // die();

    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }
}