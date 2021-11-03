<?php

namespace Runalyze\Data\Weather;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2014-09-02 at 14:39:06.
 */
class LocationTest extends TestCase {

	/**
	 * @var Location
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp(): void {
		$this->object = new Location;
	}

	public function testPosition() {
		$this->assertFalse( $this->object->hasPosition() );
		$this->object->setPosition(0, 0);
		$this->assertFalse( $this->object->hasPosition() );
		$this->object->setPosition(49.9, 7.7);
		$this->assertTrue( $this->object->hasPosition() );
	}

	public function testLocation() {
		$this->assertFalse( $this->object->hasLocationName() );
		$this->object->setLocationName('');
		$this->assertFalse( $this->object->hasLocationName() );
		$this->object->setLocationName('Kaiserslautern, de');
		$this->assertTrue( $this->object->hasLocationName() );
	}

	public function testTimestamps() {
		$this->assertFalse( $this->object->hasDateTime() );

		$this->object->setDateTime( new \DateTime() );
		$this->assertTrue( $this->object->hasDateTime() );
		$this->assertFalse( $this->object->isOlderThan() );

		$this->object->setDateTime( (new \DateTime())->setTimestamp(time() - 2*86400) );
		$this->assertTrue( $this->object->isOlderThan() );
		
		$this->object->setDateTime( (new \DateTime())->setTimestamp(time() - 7100) );
		$this->assertFalse( $this->object->isOlderThan(7200) );
	}

}
