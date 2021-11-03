<?php

namespace Runalyze\Parameter\Application;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2014-09-15 at 20:35:13.
 */
class HeartRateUnitTest extends TestCase {

	/**
	 * @var \Runalyze\Parameter\Application\HeartRateUnit
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp(): void {
		$this->object = new HeartRateUnit;
	}

	public function testValues() {
		$this->assertFalse( $this->object->isBPM() );
		$this->assertTrue( $this->object->isHRmax() );
		$this->assertFalse( $this->object->isHRreserve() );

		$this->object->set( HeartRateUnit::BPM );
		$this->assertTrue( $this->object->isBPM() );
		$this->assertFalse( $this->object->isHRmax() );
		$this->assertFalse( $this->object->isHRreserve() );

		$this->object->set( HeartRateUnit::HRRESERVE );
		$this->assertFalse( $this->object->isBPM() );
		$this->assertFalse( $this->object->isHRmax() );
		$this->assertTrue( $this->object->isHRreserve() );
	}

}
