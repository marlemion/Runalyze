<?php

namespace Runalyze\Model\Activity;

use PHPUnit\Framework\TestCase;

/**
 * Generated by hand
 */
class EntityTest extends TestCase {

	public function testEmptyObject() {
		$A = new Entity();

		$this->assertFalse($A->knowsTimezoneOffset());

		$this->assertTrue($A->weather()->isEmpty());
		$this->assertTrue($A->weather()->temperature()->isUnknown());
		$this->assertTrue($A->weather()->condition()->isUnknown());
		$this->assertTrue($A->weather()->humidity()->isUnknown());
		$this->assertTrue($A->weather()->pressure()->isUnknown());
		$this->assertTrue($A->weather()->windDegree()->isUnknown());
		$this->assertTrue($A->weather()->windSpeed()->isUnknown());

		$this->assertTrue($A->splits()->isEmpty());
		$this->assertTrue($A->partner()->isEmpty());
	}

	public function testEmptyWeatherValues() {
		$A = new Entity(array(
			Entity::TEMPERATURE => null,
			Entity::WINDDEG => null,
			Entity::WINDSPEED => null,
			Entity::PRESSURE => null,
			Entity::HUMIDITY => null
		));
		$B = new Entity(array(
			Entity::TEMPERATURE => '',
			Entity::WINDDEG => '',
			Entity::WINDSPEED => '',
			Entity::PRESSURE => '',
			Entity::HUMIDITY => ''
		));

		$this->assertTrue($A->weather()->temperature()->isUnknown());
		$this->assertTrue($B->weather()->temperature()->isUnknown());
		$this->assertTrue($A->weather()->windDegree()->isUnknown());
		$this->assertTrue($B->weather()->windDegree()->isUnknown());
		$this->assertTrue($A->weather()->windSpeed()->isUnknown());
		$this->assertTrue($B->weather()->windSpeed()->isUnknown());
		$this->assertTrue($A->weather()->pressure()->isUnknown());
		$this->assertTrue($B->weather()->pressure()->isUnknown());
		$this->assertTrue($A->weather()->humidity()->isUnknown());
		$this->assertTrue($B->weather()->humidity()->isUnknown());
	}

	public function testSynchronizationOfWeather() {
		$A = new Entity(array(
			Entity::TEMPERATURE => null,
			Entity::WINDDEG => null,
			Entity::WINDSPEED => null,
			Entity::PRESSURE => null,
			Entity::HUMIDITY => null
		));
		$A->weather()->temperature()->setTemperature(17);
		$A->weather()->windSpeed()->set(10);
		$A->weather()->windDegree()->set(180);
		$A->weather()->humidity()->set(65);
		$A->weather()->pressure()->set(1025);
		$A->synchronize();

		$this->assertEquals(17, $A->get(Entity::TEMPERATURE));
		$this->assertEquals(10, $A->get(Entity::WINDSPEED));
		$this->assertEquals(180, $A->get(Entity::WINDDEG));
		$this->assertEquals(65, $A->get(Entity::HUMIDITY));
		$this->assertEquals(1025, $A->get(Entity::PRESSURE));
	}

	public function testIsNight() {
		$EmptyEntity = new Entity([]);
		$this->assertEquals(false, $EmptyEntity->knowsIfItIsNight());
		$this->assertEquals(false, $EmptyEntity->isNight());

		$IsNotNight = new Entity([Entity::IS_NIGHT => false]);
		$this->assertEquals(true, $IsNotNight->knowsIfItIsNight());
		$this->assertEquals(false, $IsNotNight->isNight());

		$IsNight = new Entity([Entity::IS_NIGHT => true]);
		$this->assertEquals(true, $IsNight->knowsIfItIsNight());
		$this->assertEquals(true, $IsNight->isNight());

	}

}
