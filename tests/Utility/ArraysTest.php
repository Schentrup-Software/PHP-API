<?php

namespace PhpApi\Test\Utility;

use PhpApi\Utility\Arrays;
use PHPUnit\Framework\TestCase;

class ArraysTest extends TestCase
{
    public function test_getFirstElement_oneElement_getsElement(): void
    {
        $array = ['one'];
        $result = Arrays::getFirstElement($array);
        $this->assertEquals('one', $result);
    }

    public function test_getFirstElement_emptyArray_getsNull(): void
    {
        $array = [];
        $result = Arrays::getFirstElement($array);
        $this->assertNull($result);
    }

    public function test_getFirstElement_multipleElements_getsFirstElement(): void
    {
        $array = ['one', 'two', 'three'];
        $result = Arrays::getFirstElement($array);
        $this->assertEquals('one', $result);
    }

    public function test_getFirstElement_associativeArray_getsFirstElement(): void
    {
        $array = ['one' => 1, 'two' => 2, 'three' => 3];
        $result = Arrays::getFirstElement($array);
        $this->assertEquals(1, $result);
    }

    public function test_getFirstElement_oneIndexed_getsFirstElement(): void
    {
        $array = [1 => 'one', 2 => 'two', 3 => 'three'];
        $result = Arrays::getFirstElement($array);
        $this->assertEquals('one', $result);
    }

    public function test_getFirstElement_afterNextIsCalled_getsFirstElement(): void
    {
        $array = [1, 2, 3];
        next($array); // Move the internal pointer to the next element
        $this->assertEquals(2, current($array)); // Check the current element
        $result = Arrays::getFirstElement($array);
        $this->assertEquals(1, $result);
    }
}
