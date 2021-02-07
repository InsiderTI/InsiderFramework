<?php

namespace TestNamespace;

use PHPUnit\Framework\TestCase;

class TestCaseClass extends TestCase
{
    public function testShouldExecuteSystemCommand(): void
    {
        $consoleReturn = system('php --version');
        $this->assertInternalType('string', $consoleReturn);
    }
}