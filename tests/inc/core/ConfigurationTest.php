<?php

namespace Runalyze;

use PHPUnit\Framework\TestCase;
use DB;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */

class ConfigurationTest extends TestCase
{

	/** @var \PDO */
	protected $PDO;

	public function setUp(): void
	{
		$this->PDO = DB::getInstance();
		$this->PDO->exec('DELETE FROM `'.PREFIX.'conf`');
	}

	public function tearDown(): void
	{
		$this->PDO->exec('DELETE FROM `'.PREFIX.'conf`');
	}

	public function testThatResettingRequiresAccountId()
	{
		$this->expectException(\InvalidArgumentException::class);

		Configuration::loadAll(null);
		Configuration::resetConfiguration();
	}

	public function testInvalidAccountId()
	{
		$this->expectException(\InvalidArgumentException::class);

		Configuration::resetConfiguration('foo');
	}

	public function testResetConfiguration()
	{
		Configuration::loadAll(0);
		Configuration::loadAll(1);

		$this->PDO->exec('UPDATE `'.PREFIX.'conf` SET `value`="12345" WHERE `key`="PLZ" AND `accountid`=0');
		$this->PDO->exec('UPDATE `'.PREFIX.'conf` SET `value`="42" WHERE `key`="VO2MAX_FORM" AND `accountid`=0');
		$this->PDO->exec('UPDATE `'.PREFIX.'conf` SET `value`="56789" WHERE `key`="PLZ" AND `accountid`=1');

		Configuration::loadAll(0);

		$this->assertEquals('12345', Configuration::ActivityForm()->weatherLocation());
		$this->assertEquals('42', Configuration::Data()->vo2maxShape());

		Configuration::resetConfiguration(0);
		Configuration::loadAll(0);

		$this->assertEquals('', Configuration::ActivityForm()->weatherLocation());
		$this->assertEquals('42', Configuration::Data()->vo2maxShape());

		Configuration::loadAll(1);

		$this->assertEquals('56789', Configuration::ActivityForm()->weatherLocation());
	}

}
