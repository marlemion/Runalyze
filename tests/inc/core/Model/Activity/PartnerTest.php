<?php

namespace Runalyze\Model\Activity;

use PHPUnit\Framework\TestCase;

/**
 * Generated by hand
 */
class PartnerTest extends TestCase {

	public function testStringConstructor() {
		$Partner = new Partner('Max, Moritz');

		$this->assertEquals(2, $Partner->num());
		$this->assertEquals('Max', $Partner->at(0));
		$this->assertEquals('Moritz', $Partner->at(1));
		$this->assertEquals(array('Max', 'Moritz'), $Partner->asArray());
	}

	public function testTrimming() {
		$Partner = new Partner(' Max,   Moritz,Martina');

		$this->assertEquals(3, $Partner->num());
		$this->assertEquals('Moritz', $Partner->at(1));
		$this->assertEquals('Max, Moritz, Martina', $Partner->asString());
	}

}
