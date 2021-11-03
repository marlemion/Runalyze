<?php

namespace Runalyze\Calculation\HRV;

use PHPUnit\Framework\TestCase;
use Runalyze\Model\HRV\Entity;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2015-07-08 at 17:41:54.
 */

class CalculatorTest extends TestCase {

	public function testSimpleExample() {
		$Calculator = new Calculator(new Entity(array(
			Entity::DATA => array(
				500, 510, 530, 550, 560
			)
		)));
		$Calculator->calculate();

		$this->assertEquals(530, $Calculator->mean());
		$this->assertEqualsWithDelta(25.5, $Calculator->SDNN(), 0.1);
		// sqrt( (20^2 + 10^2 + 0^2 + 10^2 + 20^2 ) / 4 ) = sqrt(1000/4) = sqrt(250) = 15.8
		$this->assertEqualsWithDelta(15.8, $Calculator->RMSSD(), 0.1);
		// sqrt( (5^2 + 5^2 + 5^2 + 5^2 ) / 3 ) = sqrt(100/3) = sqrt(33.3) = 5.77
		$this->assertEqualsWithDelta(5.77, $Calculator->SDSD(), 0.1);
	}

	public function test5minAverages() {
		// This should generate averages of 1000/1010/1020/1030/1040 for five 5-min-intervals
		$data = array();
		for ($interval = 0; $interval < 5; ++$interval) {
			$value = 1000 + $interval * 10;

			for ($sum = $value; $sum <= 300*1000; $sum += $value) {
				$data[] = $value;
			}

			$data[] = $value;
		}

		$Calculator = new Calculator(new Entity(array(
			Entity::DATA => $data
		)));
		$Calculator->calculate();

		// sqrt( (20^2 + 10^2 + 0^2 + 10^2 + 20^2 ) / 4 ) = sqrt(1000/4) = sqrt(250) = 15.8
		$this->assertEqualsWithDelta(15.8, $Calculator->SDANN(), 0.1);
	}

	public function testCountings() {
		$Calculator = new Calculator(new Entity(array(
			Entity::DATA => array(500, 510, 550, 650, 610, 510, 500, 500, 400, 500)
		)));
		$Calculator->calculate();

		$this->assertEquals(6/10, $Calculator->pNN20());
		$this->assertEquals(4/10, $Calculator->pNN50());
	}

	public function testFiltering() {
		$CalculatorWithoutFilter = new Calculator(new Entity(array(
			Entity::DATA => array(300, 350, 400, 701, 99, 400, 350, 300)
		)), null);
		$CalculatorWithoutFilter->calculate();
		
		$this->assertEquals(362.5, $CalculatorWithoutFilter->mean());
		$this->assertEquals(0.0, $CalculatorWithoutFilter->percentageAnomalies());

		$CalculatorWithNormalFilter = new Calculator(new Entity(array(
			Entity::DATA => array(300, 350, 400, 701, 99, 400, 350, 300)
		)), 0.75);
		$CalculatorWithNormalFilter->calculate();
		
		$this->assertEquals(350, $CalculatorWithNormalFilter->mean());
		$this->assertEquals(0.25, $CalculatorWithNormalFilter->percentageAnomalies());

		$CalculatorWithJumpInData = new Calculator(new Entity(array(
			Entity::DATA => array(300, 350, 400, 800, 750, 700)
		)), 0.75);
		$CalculatorWithJumpInData->calculate();
		
		$this->assertEquals(550, $CalculatorWithJumpInData->mean());
		$this->assertEquals(0.0, $CalculatorWithJumpInData->percentageAnomalies());
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1902
	 */
	public function testThatInvalidDataWithZerosDoesNotThrowErrors() {
		$this->expectNotToPerformAssertions();

		$Calculator = new Calculator(new Entity(array(
			Entity::DATA => explode('|', '56909|17|18340|8193|16|18897|14|49021|0|4|18897|14|49021|0|4|18897|14|49021|0|4|0|0|0|0|54921|18897|14|49021|0|4|18897|14|49021|0|4|18897|14|49021|0|4|18897|14|49021|0|4|18897|14|49021|0|4|18897|14|49021|0|4|18897|14|49021|0')
		)));
		$Calculator->calculate();
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1899
	 */
	public function testThatLargeValuesAreIgnored() {
		$Calculator = new Calculator(new Entity(array(
			Entity::DATA => array(333, 337, 330, 338, 337, 336, 361, 321, 385, 5123)
		)));
		$Calculator->calculate();

		$this->assertEquals(342, $Calculator->mean());
		$this->assertEquals(0.1, $Calculator->percentageAnomalies());
	}
}
