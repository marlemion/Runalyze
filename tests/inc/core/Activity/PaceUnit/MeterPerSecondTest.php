<?php

namespace Runalyze\Activity\PaceUnit;

use PHPUnit\Framework\TestCase;

class MeterPerSecondTest extends TestCase
{
	public function testSomePaces()
	{
		$Pace = new MeterPerSecond();

		$this->assertEqualsWithDelta(2.78, $Pace->rawValue(360), 0.01);
		$this->assertEqualsWithDelta(3.33, $Pace->rawValue(300), 0.01);
	}

	public function testComparison()
	{
		$Pace = new MeterPerSecond();

		$this->assertEqualsWithDelta(+0.55, $Pace->rawValue($Pace->compare(300, 360)), 0.01);
		$this->assertEqualsWithDelta(-0.55, $Pace->rawValue($Pace->compare(360, 300)), 0.01);
		$this->assertEqualsWithDelta( 0.00, $Pace->rawValue($Pace->compare(300, 300)), 0.01);
	}

	public function testFormat()
	{
		AbstractDecimalUnit::$DecimalSeparator = ',';
		AbstractDecimalUnit::$Decimals = 1;

		$Pace = new MeterPerSecond();

		$this->assertEquals('3,3', $Pace->format(300));
		$this->assertEquals('4,5', $Pace->format(224));
	}
}
