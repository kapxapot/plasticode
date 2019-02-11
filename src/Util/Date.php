<?php

namespace Plasticode\Util;

class Date
{
	const DATE_FORMAT = 'Y-m-d H:i:s';
	const TIME_FORMAT_SHORT = 'H:i МСК';
	const TIME_FORMAT = 'H:i:s';
	
	const SHORT_MONTHS = [
		1 => 'Янв',
		2 => 'Фев',
		3 => 'Мар',
		4 => 'Апр',
		5 => 'Май',
		6 => 'Июн',
		7 => 'Июл',
		8 => 'Авг',
		9 => 'Сен',
		10 => 'Окт',
		11 => 'Ноя',
		12 => 'Дек',
	];
	
	const MONTHS = [
		1 => 'Январь',
		2 => 'Февраль',
		3 => 'Март',
		4 => 'Апрель',
		5 => 'Май',
		6 => 'Июнь',
		7 => 'Июль',
		8 => 'Август',
		9 => 'Сентябрь',
		10 => 'Октябрь',
		11 => 'Ноябрь',
		12 => 'Декабрь',
	];
	
	const MONTHS_GENITIVE = [
		1 => 'января',
		2 => 'февраля',
		3 => 'марта',
		4 => 'апреля',
		5 => 'мая',
		6 => 'июня',
		7 => 'июля',
		8 => 'августа',
		9 => 'сентября',
		10 => 'октября',
		11 => 'ноября',
		12 => 'декабря',
	];
	
	// null = now()
	public static function dt($date = null, $timeZone = null)
	{
	    if ($timeZone != null) {
	        $tz = new \DateTimeZone($timeZone);
	    }
	    
		return ($date instanceof \DateTime)
			? ($tz
			    ? self::toTimeZone($date, $tz)
			    : $date)
			: new \DateTime($date, $tz);
	}
	
	public static function toTimeZone($date, $timeZone)
	{
	    if (!($timeZone instanceof \DateTimeZone)) {
	        $timeZone = new \DateTimeZone($timeZone);
	    }
	    
		$copy = clone self::dt($date);
    	$copy->setTimezone($timeZone);
    	
    	return $copy;
	}
	
	public static function utc($date = null)
	{
	    return self::dt($date, 'UTC');
	}

    public static function fromUtc($utc)
    {
        return self::toTimeZone($utc, date_default_timezone_get());
    }
	
	public static function interval($interval)
	{
		return ($interval instanceof \DateInterval)
			? $interval
			: new \DateInterval($interval);
	}

	// deprecated, use dbNow
	public static function now()
	{
		return date(self::DATE_FORMAT);	
	}
	
	public static function dbNow()
	{
		return self::formatDb(self::dt());
	}
	
	// null = now()
	public static function diff($start, $end = null)
	{
		$startDate = self::dt($start);
		$endDate = self::dt($end);

		return $startDate->diff($endDate);
	}
	
	public static function age($date)
	{
		return self::diff($date);
	}
	
	public static function exceedsInterval($start, $end, $interval)
	{
		$startDate = self::dt($start);
		$endDate = self::dt($end);
		
		$first = mb_strtolower(Strings::first($interval));
		
		if ($first != 'p') {
		    $interval = date_interval_create_from_date_string($interval);
		}
		
		$interval = self::interval($interval);

		$startWithInterval = $startDate->add($interval);

		return $endDate >= $startWithInterval;
	}
	
	public static function happened($date)
	{
		if (!$date) {
			return false;
		}
		
		$now = self::dt();
		$dt = self::dt($date);
		
		return $now >= $dt;
	}
	
	public static function to($date)
	{
		if ($date) {
			$now = self::dt();
			$tomorrow = self::dt('tomorrow');
			$dayAfterTomorrow = clone $tomorrow;
			$dayAfterTomorrow->modify('1 day');

			$dt = self::dt($date);
	
			if ($dt < $tomorrow) {
				$str = 'сегодня';
			}
			elseif ($dt < $dayAfterTomorrow) {
				$str = 'завтра';
			}
			else {
				$diff = self::diff($now, $dt);
				$days = $diff->days;
				
				$cases = new Cases;
				$str = 'через ' . $days . ' ' . $cases->caseForNumber('день', $days);
			}
		}
		
		return $str ?? 'неизвестно когда';
	}
	
	public static function toAgo($date, $lang = null)
	{
		if ($date) {
			$now = self::dt();
			
			$dayAgo = clone $now;
			$dayAgo->modify('-1 day');
			
			$hourAgo = clone $now;
			$hourAgo->modify('-1 hour');

			$dt = self::dt($date);
			$age = self::diff($dt, $now);
			
			$cases = new Cases;
	
			if ($dt > $hourAgo) {
			    $minutes = $age->i;
				$str = $minutes . ' ' .
				    (($lang == 'en')
				        ? 'minute' . ($minutes > 1 ? 's' : '') . ' ago'
				        : $cases->caseForNumber('минута', $minutes) . ' назад');
			}
			elseif ($dt > $dayAgo) {
			    $hours = $age->h;
				$str = $hours . ' ' .
				    (($lang == 'en')
				        ? 'hour' . ($hours > 1 ? 's' : '') . ' ago'
				        : $cases->caseForNumber('час', $hours) . ' назад');
			}
			else {
				$days = $age->days;
				$str = $days . ' ' .
				    (($lang == 'en')
				        ? 'day' . ($days > 1 ? 's' : '') . ' ago'
				        : $cases->caseForNumber('день', $days) . ' назад');
			}
		}
		
		return $str ?? (($lang == 'en') ? 'never' : 'неизвестно когда');
	}
	
	public static function startOfHour($date)
	{
		$copy = clone self::dt($date);
		$hour = self::hour($copy);
		$copy->setTime($hour, 0, 0);
		
		return $copy;
	}
	
	public static function stripTime($date)
	{
		$copy = clone self::dt($date);
		$copy->setTime(0, 0, 0);
		
		return $copy;
	}
	
	public static function startOfDay($date)
	{
	    return self::stripTime($date);
	}

	public static function endOfDay($date)
	{
		$copy = clone self::dt($date);
		$copy->setTime(23, 59, 59);
		
		return $copy;
	}
	
	public static function format($date, $format = null)
	{
		return self::dt($date)->format($format ?? self::DATE_FORMAT);
	}
	
	public static function formatDb($date)
	{
		return self::dt($date)->format(self::DATE_FORMAT);
	}
	
	public static function formatIso($date)
	{
		return ($date instanceof \DateTime)
			? $date->format('c')
			: strftime('%FT%T%z', $date);
	}
	
	public static function generateExpirationTime($minutes = 60)
	{
		return date(self::DATE_FORMAT, strtotime("+{$minutes} minutes"));
	}
	
	const TIME_OFF = 0;
	const TIME_SOFT = 1;
	const TIME_HARD = 2;
	
	const YEAR_OFF = 0;
	const YEAR_ON = 1;
	const YEAR_EXTRA = 2;
	const YEAR_EXPLICIT = 3; // not used
	const YEAR_EXPLICIT_EXTRA = 4; // not used
	
	const MONTH_OFF = 0;
	const MONTH_ON = 1;
	const MONTH_NUM = 2;
	
	public static function formatIntervalUi($start, $end, $timeMode = self::TIME_OFF, $monthMode = self::MONTH_ON, $yearMode = self::YEAR_ON)
	{
		if (!$start || !$end) {
			return self::formatUi($start ?? $end, $timeMode, $monthMode, $yearMode);
		}

		// 30 декабря 2018 — 10 января 2019
		// 30 декабря 2018 г. — 10 января 2019 г.
		// 10–20 января 2018 г.
		// 10–20 января 2018
		// 10–20 января
		// 10 января — 20 февраля 2018
		// 10 января — 20 февраля 2018 г.
		// 10 января — 20 февраля
		$sameDay = (self::day($start) === self::day($end));
		$sameMonth = (self::month($start) === self::month($end));
		$sameYear = (self::year($start) === self::year($end));
		
		$firstMonthMode = ($sameMonth && $sameYear)
			? self::MONTH_OFF
			: $monthMode;
		
		$firstYearMode = ($yearMode !== self::YEAR_OFF && !$sameYear)
			? $yearMode
			: self::YEAR_OFF;
		
		$result = self::formatUi($end, $timeMode, $monthMode, $yearMode);

		if ($firstMonthMode === self::MONTH_OFF) {
			if (!$sameDay) {
				$result = self::day($start) . '–' . $result;
			}
		}
		else {
			$result = self::formatUi($start, $timeMode, $firstMonthMode, $firstYearMode) . ' — ' . $result;
		}
		
		return $result;
	}
	
	public static function formatDateUi($date)
	{
		return self::formatUi($date, self::TIME_OFF);
	}
	
	public static function formatUi($date, $timeMode = self::TIME_SOFT, $monthMode = self::MONTH_ON, $yearMode = self::YEAR_ON)
	{
		$dt = self::dt($date);
		
		$parts = [ self::day($dt) ];
		
		if ($monthMode !== self::MONTH_OFF) {
			$month = self::month($dt);
			
			if ($monthMode === self::MONTH_ON) {
				$month = self::MONTHS_GENITIVE[$month];
			}
			
			$parts[] = $month;
		}

		if ($yearMode !== self::YEAR_OFF) {
			$year = self::year($dt);
			
			if ($yearMode === self::YEAR_EXTRA) {
				$year .= ' г.';
			}
			
			$parts[] = $year;
		}
		
		$delimiter = ($monthMode === self::MONTH_NUM) ? '.' : '&nbsp;';
		$result = implode($delimiter, $parts);

		if ($timeMode === self::TIME_HARD || $timeMode === self::TIME_SOFT && self::hasTime($dt)) {
			if ($monthMode === self::MONTH_ON) {
				$result .= ',';
			}
			
			$result .= '&nbsp;' . self::formatTime($dt);
		}

		return $result;
	}
	
	private static function part($date, $part)
	{
		return intval(self::dt($date)->format($part));
	}
	
	public static function day($date)
	{
		return self::part($date, 'd');
	}
	
	public static function month($date)
	{
		return self::part($date, 'm');
	}
	
	public static function year($date)
	{
		return self::part($date, 'Y');
	}
	
	public static function hour($date)
	{
		return self::part($date, 'H');
	}
	
	public static function minute($date)
	{
		return self::part($date, 'i');
	}
	
	public static function second($date)
	{
		return self::part($date, 's');
	}
	
	public static function hasTime($date)
	{
		return self::hour($date) + self::minute($date) + self::second($date) > 0;
	}
		
	public static function formatTime($date, $withSeconds = false)
	{
		$format = $withSeconds
			? self::TIME_FORMAT
			: self::TIME_FORMAT_SHORT;
			
		return self::dt($date)->format($format);
	}
}
