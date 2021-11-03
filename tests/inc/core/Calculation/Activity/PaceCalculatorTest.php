<?php

namespace Runalyze\Calculation\Activity;

use PHPUnit\Framework\TestCase;
use Runalyze\Model\Trackdata\Entity;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2015-01-21 at 09:17:29.
 */

class PaceCalculatorTest extends TestCase {

	public function testNoDistanceGiven() {
		$Calculator = new PaceCalculator(new Entity(array(
			Entity::TIME => array(0, 7, 16, 20, 24, 25, 30, 40, 60, 66)
		)));

		$this->assertEquals(array(), $Calculator->calculate());
	}

	public function testNothingSpecialToDo() {
		$Calculator = new PaceCalculator(new Entity(array(
			Entity::TIME => array(0, 7, 16, 20, 24, 25, 30, 40, 60, 66),
			Entity::DISTANCE => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9)
		)));

		$this->assertEquals(array(
			7, 7, 9, 4, 4, 1, 5, 10, 20, 6
		), $Calculator->calculate());
	}

	public function testSmoothPaceForRuntasticData() {
		$Calculator = new PaceCalculator(new Entity(array(
			Entity::TIME => array(0, 25, 27, 31, 32, 35, 37, 39, 45, 50),
			Entity::DISTANCE => array(0.0, 0.0, 0.0, 0.052, 0.052, 0.052, 0.052, 0.052, 0.071, 0.085)
		)));

		$this->assertEquals(array(
			596, 596, 596, 596, 737, 737, 737, 737, 737, 357
		), $Calculator->calculate());
	}

}
