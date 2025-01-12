<?php

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

use PHPUnit\Framework\TestCase;

class KernelsTest extends TestCase
{
    public function testInvalidKernel()
    {
    	$this->expectException(\InvalidArgumentException::class);

        Kernels::get(-1, 5);
    }

    public function testAllConstructors()
    {
        $this->expectNotToPerformAssertions();

        foreach (Kernels::getEnum() as $kernelid) {
            Kernels::get($kernelid, 5.0);
        }
    }

    public function testNormalizations()
    {
        $x = range(-1.0, 1.0, 0.01);
        $num = count($x);

        foreach (Kernels::getEnum() as $kernelid) {
            $Kernel = Kernels::get($kernelid, 2.0);
            $sum = array_sum($Kernel->valuesAt($x, true));

            $this->assertEqualsWithDelta(1.0, $sum / $num, 0.02, 'Kernel with id "'.$kernelid.'" is not normalized, sum is '.$sum);
        }
    }
}
