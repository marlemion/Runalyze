<?php

namespace Runalyze\Model\Activity\Splits;

use PHPUnit\Framework\TestCase;

/**
 * Generated by hand
 */
class CompletorForDistanceTest extends TestCase {

	public function testCompletor() {
		$Splits = new Entity(array(
			new Split(0, 300),
			new Split(0, 300)
		));

		$Completor = new CompletorForDistance($Splits,
			array(100, 200, 300, 400, 500, 600),
			array(0.3, 0.6, 1.0, 1.4, 1.9, 2.3)
		);
		$Completor->completeSplits();

		$this->assertEquals(1.0, $Splits->at(0)->distance());
		$this->assertEquals(1.3, $Splits->at(1)->distance());
	}

}
