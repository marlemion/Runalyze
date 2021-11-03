<?php

namespace Runalyze\Tests\Metrics\Common;

use PHPUnit\Framework\TestCase;
use Runalyze\Metrics\Common\BaseUnitTrait;

class BaseUnitTraitTest extends TestCase
{
    public function testThatConversionWorksAsExpected()
    {
        /** @var BaseUnitTrait $mock */
        $mock = $this->getMockForTrait(BaseUnitTrait::class);

        $this->assertEquals(3.14, $mock->fromBaseUnit(3.14));
        $this->assertEquals(42.195, $mock->toBaseUnit(42.195));
    }
}
