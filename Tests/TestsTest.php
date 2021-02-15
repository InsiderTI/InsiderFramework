<?php

use PHPUnit\Framework\TestCase;

final class TestsTest extends TestCase
{
    public function testShouldRun(): void
    {
        $this->assertEquals(
            1,
            1
        );
    }
}
