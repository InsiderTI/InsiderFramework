<?php

declare(strict_types=1);
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
