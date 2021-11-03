<?php

namespace Runalyze\Tests\Metrics\Common;

use PHPUnit\Framework\TestCase;
use Runalyze\Metrics\Common\AbstractMetric;

class AbstractMetricTest extends TestCase
{
    public function testThatConversionWorksAsExpected()
    {
        /** @var AbstractMetric $mock */
        $mock = $this->getMockForAbstractClass(AbstractMetric::class);

        $this->assertFalse($mock->isKnown());
        $this->assertEquals(42, $mock->setValue(42)->getValue());
        $this->assertTrue($mock->isKnown());
        $this->assertNull($mock->setValue(null)->getValue());
        $this->assertFalse($mock->isKnown());
    }
}
