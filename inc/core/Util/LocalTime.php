<?php
/**
 * This file contains class::LocalTime
 * @package Runalyze\Util
 */

namespace Runalyze\Util;

use Runalyze\Configuration;

/**
 * Local time
 *
 * Local time means: 5pm is always 5pm - no matter in which time zone.
 * Objects of this class will forget every time zone information at construction,
 * which is internally done by assuming 'UTC' as given time zone.
 *
 * Don't use timestamps generated by this class in another time zone and don't
 * compare them with timestamps in any time zone different to 'UTC'!
 *
 * @author Michael Pohl
 * @package Runalyze\Util
 */
class LocalTime extends \DateTime
{
    /**
     * Construct local time
     *
     * This DateTime object assumes that the given time is in utc (+00:00),
     * i.e. no timezone information is needed to get its daytime.
     *
     * @param int|null $timestampInNoTimezone timestamp, it is assumed that this timestamp is correctly shown in +00:00
     * @throws \InvalidArgumentException
     */
    public function __construct($timestampInNoTimezone = null)
    {
        if (null === $timestampInNoTimezone) {
            parent::__construct(null, new \DateTimeZone('UTC'));
        } else {
            if (!is_numeric($timestampInNoTimezone)) {
                throw new \InvalidArgumentException('Given timestamp must be numeric.');
            }

            parent::__construct('@'.$timestampInNoTimezone);

            $this->setTimezone(new \DateTimeZone('UTC'));
        }
    }

    /**
     * @param int $timestampInServerTimezone timestamp (created by time(), mktime(), ...) that will be corrected
     * @return \Runalyze\Util\LocalTime
     * @throws \InvalidArgumentException
     */
    public static function fromServerTime($timestampInServerTimezone)
    {
        if (!is_numeric($timestampInServerTimezone)) {
            throw new \InvalidArgumentException('Given timestamp must be numeric.');
        }

        $correctedTimestamp = $timestampInServerTimezone + (new \DateTime())->setTimestamp($timestampInServerTimezone)->getOffset();

        return new self($correctedTimestamp);
    }

    /**
     * @see http://php.net/manual/en/datetime.formats.php
     * @param string $dateTimeString date/time string, any timezone information (like +02:00) will be ignored
     * @return \Runalyze\Util\LocalTime
     */
    public static function fromString($dateTimeString)
    {
        $DateTime = new \DateTime($dateTimeString);

        return new self($DateTime->getTimestamp() + $DateTime->getOffset());
    }

    /**
     * @return \DateTime
     */
    public function toServerTime()
    {
        return new \DateTime($this->format('d.m.Y H:i:s'));
    }

    /**
     * @return int
     */
    public function toServerTimestamp()
    {
        return $this->toServerTime()->getTimestamp();
    }

    /**
     * Get the timestamp of the start of the week
     * @param bool $asObject set to true to get a new object as result
     * @return int|\Runalyze\Util\LocalTime
     */
    public function weekstart($asObject = false)
    {
        $Date = clone $this;
        $w = $Date->format("w");

        if (Configuration::General()->weekStart()->isMonday()) {
            if ($w == 0) {
                $w = 6;
            } else {
                $w -= 1;
            }
        }

        $Date->sub(new \DateInterval('P'.$w.'D'));
        $Date->setTime(0, 0, 0);

        return $asObject ? $Date : $Date->getTimestamp();
    }

    /**
     * Get the timestamp of the end of the week
     * @param bool $asObject set to true to get a new object as result
     * @return int|\Runalyze\Util\LocalTime
     */
    public function weekend($asObject = false)
    {
        $Date = $this->weekstart(true);
        $Date->add(new \DateInterval('P6D'));
        $Date->setTime(23, 59, 50);

        return $asObject ? $Date : $Date->getTimestamp();
    }

    /**
     * @return int
     */
    public function week()
    {
        return Configuration::General()->weekStart()->phpWeek($this->toServerTimestamp());
    }

    /**
     * Get the timestamp of the start of the month
     * @param bool $asObject set to true to get a new object as result
     * @return int|\Runalyze\Util\LocalTime
     */
    public function monthStart($asObject = false)
    {
        $Date = clone $this;
        $Date->setDate((int)$this->format('Y'), (int)$this->format('m'), 1)->setTime(0, 0, 0);

        return $asObject ? $Date : $Date->getTimestamp();
    }

    /**
     * Get the timestamp of the end of the month
     * @param bool $asObject set to true to get a new object as result
     * @return int|\Runalyze\Util\LocalTime
     */
    public function monthEnd($asObject = false)
    {
        $Date = clone $this;
        $Date->setDate((int)$this->format('Y'), (int)$this->format('m') + 1, 0)->setTime(23, 59, 59);

        return $asObject ? $Date : $Date->getTimestamp();
    }

    /**
     * Get the timestamp of the start of the month
     * @param bool $asObject set to true to get a new object as result
     * @return int|\Runalyze\Util\LocalTime
     */
    public function yearStart($asObject = false)
    {
        $Date = clone $this;
        $Date->setDate((int)$this->format('Y'), 1, 1)->setTime(0, 0, 0);

        return $asObject ? $Date : $Date->getTimestamp();
    }

    /**
     * Get the timestamp of the end of the month
     * @param bool $asObject set to true to get a new object as result
     * @return int|\Runalyze\Util\LocalTime
     */
    public function yearEnd($asObject = false)
    {
        $Date = clone $this;
        $Date->setDate((int)$this->format('Y'), 12, 31)->setTime(23, 59, 59);

        return $asObject ? $Date : $Date->getTimestamp();
    }

    /**
     * Is given timestamp from today?
     * @return bool
     */
    public function isToday()
    {
        return (date('d.m.Y') == $this->format('d.m.Y'));
    }

    /**
     * LocalTime equivalent for time()
     * @return int
     */
    public static function now()
    {
        return self::fromServerTime(time())->getTimestamp();
    }

    /**
     * LocalTime equivalent for date($format[, $timestamp])
     * @param string $format
     * @param int|null $timestamp
     * @return string
     */
    public static function date($format, $timestamp = null)
    {
        if (null === $timestamp) {
            return self::fromServerTime(time())->format($format);
        }

        return (new self($timestamp))->format($format);
    }

    /**
     * LocalTime equivalent for mktime(...)
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param int $month
     * @param int $day
     * @param int $year
     * @return int
     */
    public static function mktime($hour, $minute, $second, $month, $day, $year)
    {
        return self::fromServerTime(mktime($hour, $minute, $second, $month, $day, $year))->getTimestamp();
    }
}
