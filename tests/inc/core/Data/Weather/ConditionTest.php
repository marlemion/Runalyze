<?php

namespace Runalyze\Data\Weather;

use PHPUnit\Framework\TestCase;
use Runalyze\Profile\Weather\WeatherConditionProfile;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2014-09-27 at 18:55:20.
 */

class ConditionTest extends TestCase {

	/**
	 * @var Condition
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp(): void {
		$this->object = new Condition(0);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown(): void {

	}

	/**
	 * @covers Runalyze\Data\Weather\Condition::completeList
	 * @covers Runalyze\Data\Weather\Condition::set
	 * @covers Runalyze\Data\Weather\Condition::id
	 * @covers Runalyze\Data\Weather\Condition::icon
	 * @covers Runalyze\Data\Weather\Condition::string
	 */
	public function testConditions() {
		foreach (Condition::completeList() as $id) {
			$this->object->set($id);

			$this->assertEquals($id, $this->object->id());
			$this->assertInstanceOf('\Runalyze\View\Icon', $this->object->icon());
			$this->assertNotEmpty($this->object->string());
		}
	}

	public function testUnknown() {
		$this->object->set(WeatherConditionProfile::UNKNOWN);

		$this->assertTrue($this->object->isUnknown());
	}

	public function testWrongId() {
		$this->object->set('foobar');
		$this->assertTrue($this->object->isUnknown());
	}

}
