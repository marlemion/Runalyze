<?php

namespace Runalyze\Tests\Mathematics\PointReduction;

use PHPUnit\Framework\TestCase;
use Runalyze\Mathematics\PointReduction\AbstractPointReductionAlgorithm;

class AbstractPointReductionAlgorithmTest extends TestCase
{
    public function testPerpendicularDistanceForPointsOutsideTheLinesXRegion()
    {
        $this->assertEquals(5.0, AbstractPointReductionAlgorithm::shortestDistance(
            -4.0, 3.0, 0.0, 0.0, 2.0, 2.0
        ));
        $this->assertEqualsWithDelta(4.95, AbstractPointReductionAlgorithm::perpendicularDistance(
            -4.0, 3.0, 0.0, 0.0, 2.0, 2.0
        ), 0.005);
    }

    public function testDistanceForPointsOnTheLine()
    {
        $this->assertEquals(0.0, AbstractPointReductionAlgorithm::shortestDistance(
            1.0, 1.0, 0.0, 0.0, 2.0, 2.0
        ));
        $this->assertEquals(0.0, AbstractPointReductionAlgorithm::perpendicularDistance(
            1.0, 1.0, 0.0, 0.0, 2.0, 2.0
        ));

        $this->assertEquals(0.0, AbstractPointReductionAlgorithm::shortestDistance(
            1.0, 1.0, 1.0, 1.0, 2.0, 2.0
        ));
        $this->assertEquals(0.0, AbstractPointReductionAlgorithm::perpendicularDistance(
            1.0, 1.0, 1.0, 1.0, 2.0, 2.0
        ));

        $this->assertEquals(0.0, AbstractPointReductionAlgorithm::shortestDistance(
            2.0, 2.0, 2.0, 2.0, 0.0, 0.0
        ));
        $this->assertEquals(0.0, AbstractPointReductionAlgorithm::perpendicularDistance(
            2.0, 2.0, 2.0, 2.0, 0.0, 0.0
        ));
    }

    public function testDistanceToHorizontalLine()
    {
        $this->assertEquals(2.3, AbstractPointReductionAlgorithm::shortestDistance(
            6.9, 5.5, 0.0, 3.2, 10.0, 3.2
        ));
        $this->assertEquals(2.3, AbstractPointReductionAlgorithm::perpendicularDistance(
            6.9, 5.5, 0.0, 3.2, 10.0, 3.2
        ));
    }

    public function testDistanceToSomeUnspecificLine()
    {
        list($line1x, $line1y, $line2x, $line2y) = [1.0, 1.0, 3.0, 2.0];

        $this->assertEqualsWithDelta(0.89, AbstractPointReductionAlgorithm::shortestDistance(
            1.0, 2.0, $line1x, $line1y, $line2x, $line2y
        ), 0.005);
        $this->assertEqualsWithDelta(0.89, AbstractPointReductionAlgorithm::perpendicularDistance(
            1.0, 2.0, $line1x, $line1y, $line2x, $line2y
        ), 0.005);

        $this->assertEqualsWithDelta(0.45, AbstractPointReductionAlgorithm::shortestDistance(
            2.0, 2.0, $line1x, $line1y, $line2x, $line2y
        ), 0.005);
        $this->assertEqualsWithDelta(0.45, AbstractPointReductionAlgorithm::perpendicularDistance(
            2.0, 2.0, $line1x, $line1y, $line2x, $line2y
        ), 0.005);
    }
}
