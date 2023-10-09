<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class EqualsSameTest extends TestCase
{
    public function testEquals(): void
    {
        $this->assertEquals('1', 1);
    }

    public function testSame(): void
    {
        $this->assertSame(1, 1);
    }
}