<?php

namespace Runalyze\Model;

class StringObject_MockTester extends StringObject {
	protected $String;
	public function fromString($string) {
		$this->String = $string;
	}
	public function asString() {
		return $this->String;
	}
}

use PHPUnit\Framework\TestCase;

/**
 * Generated by hand
 */
class StringObjectTest extends TestCase {

	public function testConstructor() {
		$String = new StringObject_MockTester('foo');

		$this->assertEquals('foo', $String->asString());
	}

	public function testSimpleStringObject() {
		$String = new StringObject_MockTester();
		$String->fromString('foo');

		$this->assertEquals('foo', $String->asString());
		$this->assertFalse($String->isEmpty());
	}

	public function testEmptyObject() {
		$String = new StringObject_MockTester();

		$this->assertTrue($String->isEmpty());
		$String->fromString('foo');
		$this->assertFalse($String->isEmpty());
		$String->fromString('');
		$this->assertTrue($String->isEmpty());
	}

}
