<?php

namespace Runalyze\Parameter\Application;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2014-09-15 at 20:35:06.
 */
class ElevationMethodTest extends TestCase {

	/**
	 * @var \Runalyze\Parameter\Application\ElevationMethod
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp(): void {
		$this->object = new ElevationMethod;
	}

	public function testMethods() {
		$this->object->set( ElevationMethod::NONE );
		$this->assertTrue( $this->object->usesNone() );
		$this->assertFalse( $this->object->usesThreshold() );
		$this->assertFalse( $this->object->usesDouglasPeucker() );

		$this->object->set( ElevationMethod::THRESHOLD );
		$this->assertFalse( $this->object->usesNone() );
		$this->assertTrue( $this->object->usesThreshold() );
		$this->assertFalse( $this->object->usesDouglasPeucker() );

		$this->object->set( ElevationMethod::DOUGLAS_PEUCKER );
		$this->assertFalse( $this->object->usesNone() );
		$this->assertFalse( $this->object->usesThreshold() );
		$this->assertTrue( $this->object->usesDouglasPeucker() );
	}

}
