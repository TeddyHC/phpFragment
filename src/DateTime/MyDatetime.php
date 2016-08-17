<?php

class MyDateTimeException extends SystemException
{
    const FORMAT_DATETIME_ERROR = 'ת��ʱ������ʧ��';
}

class MyDateTime
{
    const SECOND_OF_MINUTE = 60;
    const SECOND_OF_HOUR = 3600;
    const SECOND_OF_DAY = 86400;

    const DEFAULT_DATE_FORMAT = 'Y-m-d';
    const DEFAULT_TIME_FORMAT = 'H:i:s';
    const DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i:s';
    const DEFAULT_DATETIME_SIMPLE_FORMAT = 'y-m-d H:i';
    const DEFAULT_MICROTIME_FORMAT = 'Y-m-d H:i:s:x';

    const DEFAULT_START_DATETIME = '1970-01-01 00:00:00';  //Unix timestamp
    const UNDEFINE_DATETIME = '0000-00-00 00:00:00';
    const INFINITY_DATETIME = '9999-12-31 23:59:59';

    const ZERO = 0;
    const YEAR = 'Y';
    const MONTH = 'm';
    const DAY = 'd';
    const HOUR = 'H';
    const MINUTE = 'i';
    const SECOND = 's';

    private static $weeks = array('1' => '����һ', '2' => '���ڶ�', '3' => '������', '4' => '������', '5' => '������', '6' => '������', '7' => '������');
    private static $workingDays = array('1' => '����һ', '2' => '���ڶ�', '3' => '������', '4' => '������', '5' => '������');
    private static $nowForTest = null;
    private $date;  //����string

    //TODO:ҵ���߼����
    public static $holiday = array(
        0 => array('beginTime' => '2016-01-01 00:00:00', 'endTime' => '2016-01-03 23:59:59'),
        1 => array('beginTime' => '2016-02-07 00:00:00', 'endTime' => '2016-02-13 23:59:59'),
        2 => array('beginTime' => '2016-04-02 00:00:00', 'endTime' => '2016-04-04 23:59:59'),
        3 => array('beginTime' => '2016-04-30 00:00:00', 'endTime' => '2016-05-02 23:59:59'),
        4 => array('beginTime' => '2016-06-09 00:00:00', 'endTime' => '2016-06-11 23:59:59'),
        5 => array('beginTime' => '2016-09-15 00:00:00', 'endTime' => '2016-09-17 23:59:59'),
        6 => array('beginTime' => '2016-10-01 00:00:00', 'endTime' => '2016-10-07 23:59:59'),
    );

    protected function __construct($date)
    {
        $this->date = $date;
    }

    //TODO:can't add params
    public function __toString()
    {
        return $this->toStringByFormat();
    }

    public static function today($format = self::DEFAULT_DATE_FORMAT)
    {
        return self::valueOf(date($format));
    }

    public static function now($format = self::DEFAULT_DATETIME_FORMAT)
    {
        if (self::$nowForTest !== null) {
            return self::$nowForTest;
        }

        return self::valueOf(date($format));
    }

    public static function createMyDateTime($year, $month, $day, $hour = '00', $minute = '00', $second = '00')
    {
        $time = mktime($hour, $minute, $second, $month, $day, $year);
        if ($time == false) {
            throw new MyDateTimeException(MyDateTimeException::FORMAT_DATETIME_ERROR);
        }
        $date = date(self::DEFAULT_DATETIME_FORMAT, $time);

        return new self($date);
    }

    public static function valueOf($date)
    {
        if ($date == self::DEFAULT_UNDEFINE_TIME || $date == '0000-00-00' || $date == '0') {
            return new self(0);
        }
        if (strtotime($date) === false) {
            throw new MyDateTimeException(MyDateTimeException::FORMAT_DATETIME_ERROR);
        }

        return new self($date);
    }

    public static function valueOfTime($timestamp)
    {
        //  DBC::requireNotEmptyString($timestamp, "ʱ�������Ϊ��");

        $date = date(self::DEFAULT_DATETIME_FORMAT, $timestamp);

        return new self($date);
    }

    public static function setNowForTest(MyDateTime $now)
    {
        self::$nowForTest = $now;
    }

    public static function cleanNowForTest()
    {
        self::$nowForTest = null;
    }

    public static function isNowForTest()
    {
        return self::$nowForTesti === null;
    }

    public static function setChinaTimeZone()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function yesterday($format = self::DEFAULT_DATE_FORMAT)
    {
        return new self(date($format, time() - self::SECOND_OF_DAY));
    }

    public static function dayBeforeYesterday($format = self::DEFAULT_DATE_FORMAT)
    {
        return new self(date($format, time() - self::SECOND_OF_DAY * 2));
    }

    public static function tomorrow($format = self::DEFAULT_DATE_FORMAT)
    {
        return new self(date($format, time() + self::SECOND_OF_DAY));
    }

    public function before($xDateTime)
    {
        return $this->getTime() < $xDateTime->getTime();
    }

    public function after($xDateTime)
    {
        return $this->getTime() > $xDateTime->getTime();
    }

    public function getDateString()
    {
        return $this->date;
    }

    public function getTime()
    {
        return strtotime($this->date);
    }

    public function addYear($year)
    {
        $year = $this->getYear() + $year;
        $month = $this->getMonth();
        $day = $this->getDay();
        $hour = $this->getHour();
        $minute = $this->getMinute();
        $second = $this->getSecond();

        return self::createMyDateTime($year, $month, $day, $hour, $minute, $second);
    }

    public function addMonth($month)
    {
        $year = $this->getYear();
        $month = $this->getMonth() + $month;
        $day = $this->getDay();
        $hour = $this->getHour();
        $minute = $this->getMinute();
        $second = $this->getSecond();

        return self::createMyDateTime($year, $month, $day, $hour, $minute, $second);
    }

    public function addDay($day)
    {
        return $this->addSecond($day * self::SECOND_OF_DAY);
    }

    public function addHour($hour)
    {
        return $this->addSecond($hour * self::SECOND_OF_HOUR);
    }

    public function addMinute($minute)
    {
        return $this->addSecond($minute * self::SECOND_OF_MINUTE);
    }

    public function addSecond($second)
    {
        $time = $this->getTime() + $second;

        return self::valueOf(date(self::DEFAULT_XDATETIME_FORMAT, $time));
    }

    public function setYear($year)
    {
        return $this->setDate(self::YEAR, $year);
    }

    public function setMonth($month)
    {
        return $this->setDate(self::MONTH, $month);
    }

    public function setDay($day)
    {
        return $this->setDate(self::DAY, $day);
    }

    public function setHour($hour)
    {
        return $this->setDate(self::HOUR, $hour);
    }

    public function setMinute($minute)
    {
        return $this->setDate(self::MINUTE, $minute);
    }

    public function setSecond($second)
    {
        return $this->setDate(self::SECOND, $second);
    }

    private function setDate($format, $setNum)
    {
        $year = $this->getYear();
        $month = $this->getMonth();
        $day = $this->getDay();
        $hour = $this->getHour();
        $minute = $this->getMinute();
        $second = $this->getSecond();
        switch ($format) {
        case self::YEAR :
            $year = $setNum;
            break;
        case self::MONTH :
            $month = $setNum;
            break;
        case self::DAY :
            $day = $setNum;
            break;
        case self::HOUR :
            $hour = $setNum;
            break;
        case self::MINUTE :
            $minute = $setNum;
            break;
        case self::SECOND :
            $second = $setNum;
            break;
        }

        return self::createMyDateTime($year, $month, $day, $hour, $minute, $second);
    }

    public function getDateDiff(MyDateTime $nowDate)
    {
        $arrNow = getdate(strtotime($nowDate));
        $arrCurrent = getdate($this->getTime());

        $yearDiff = $arrNow['year'] - $arrCurrent['year'];
        $monthDiff = $arrNow['mon'] - $arrCurrent['mon'];
        $dayDiff = $arrNow['mday'] + 1 - $arrCurrent['mday'];
        //�������
        if ($yearDiff > 0
            && $arrNow['mon'] == 2
            && $arrCurrent['mon'] == 2
            && $arrCurrent['mday'] >= 28
            && $arrNow['mday'] >= 28
        ) {
            $current2Days = $this->getDaysOfMonth($arrCurrent['year'], $arrCurrent['mon']);
            $now2Days = $this->getDaysOfMonth($arrNow['year'], $arrNow['mon']);
            if ($current2Days != $now2Days) {
                if ($current2Days == 29) {
                    $dayDiff = 0;
                }
            }
        }

        if ($dayDiff < 0) {
            --$monthDiff;
            $monthDays = $this->getDaysOfMonth($arrCurrent['year'], $arrCurrent['mon']);
            $dayDiff = $arrNow['mday'] + ($monthDays - $arrCurrent['mday']);
        }

        if ($monthDiff < 0) {
            --$yearDiff;
            $monthDiff = 12 + $monthDiff;
        }

        return array('year' => $yearDiff, 'month' => $monthDiff, 'day' => $dayDiff);
    }

    //TODO:is pool
    public static function getDaysOfMonth($year, $month)
    {
        $days = 0;
        switch ($month) {
        case 1:
        case 3:
        case 5:
        case 7:
        case 8:
        case 10:
        case 12:
            $days = 31;
            break;
        case 2:
            if (($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0) {
                $days = 29;
            } else {
                $days = 28;
            }
            break;
        default:
            $days = 30;
            break;
        }

        return $days;
    }

    public function getYear()
    {
        return date('Y', $this->getTime());
    }

    public function getMonth()
    {
        return date('m', $this->getTime());
    }

    public function getDay()
    {
        return date('d', $this->getTime());
    }

    public function getHour()
    {
        return date('H', $this->getTime());
    }

    public function getMinute()
    {
        return date('i', $this->getTime());
    }

    public function getSecond()
    {
        return date('s', $this->getTime());
    }

    public function getMonthAndDay()
    {
        return date('m-d', $this->getTime());
    }

    public function getDate()
    {
        return date('Y-m-d', $this->getTime());
    }

    public function getWeekDesc()
    {
        $index = (int) date('N', $this->getTime());

        return self::$weeks["$index"];
    }

    //TODO:get out
    public function isWorkingDay()
    {
        $index = (int) date('N', $this->getTime());

        return array_key_exists($index, self::$workingDays);
    }

    public static function yearDiff(MyDateTime $d1, MyDateTime $d2)
    {
        return $d2->getYear() - $d1->getYear();
    }

    public static function monthDiff(MyDateTime $d1, MyDateTime $d2)
    {
        $diff = self::yearDiff($d1, $d2) * 12 + ($d2->getMonth() - $d1->getMonth());
        if ($d2->getDay() - $d1->getDay() < 0) {
            $diff--;
        }

        return $diff;
    }

    public static function dayDiff(MyDateTime $d1, MyDateTime $d2)
    {
        return floor(($d2->getTime() - $d1->getTime()) / self::SECOND_OF_DAY);
    }

    public static function hourDiff(MyDateTime $d1, MyDateTime $d2)
    {
        return floor(($d2->getTime() - $d1->getTime()) / self::SECOND_OF_HOUR);
    }

    public static function minuteDiff(MyDateTime $d1, MyDateTime $d2)
    {
        return floor(($d2->getTime() - $d1->getTime()) / self::SECOND_OF_MINUTE);
    }

    public static function secondDiff(MyDateTime $d1, MyDateTime $d2)
    {
        return floor(($d2->getTime() - $d1->getTime()));
    }

    public static function addDaysFromNow($days)
    {
        return new self(date(self::DEFAULT_XDATETIME_FORMAT, time() + (int) $days * self::SECOND_OF_DAY));
    }

    public function between($beginDate, $endDate)
    {
        return $this->getTime() >= $beginDate->getTime() && $this->getTime() <= $endDate->getTime();
    }

    public static function afterToday($date)
    {
        if (empty($date) || ($date == '')) {
            return true;
        }
        if (is_string($date)) {
            $date = strtotime($date);
        }

        return $date > time();
    }

    public static function getYearsToToday($from = 1949)
    {
        $years = array();
        $currentYear = (int) date('Y', time());
        for ($year = $from; $year <= $currentYear; ++$year) {
            $years[strval($year)] = strval($year);
        }

        return $years;
    }

    public function toString()
    {
        return $this->toStringByFormat();
    }

    public function toShortString()
    {
        return $this->toStringByFormat(self::DEFAULT_DATE_FORMAT);
    }

    public function toStringByFormat($format = 'Y-m-d H:i:s')
    {
        return date($format, $this->getTime());
    }

    public function equals($otherMyDateTime)
    {
        if (is_object($otherMyDateTime) && (get_class($this) == get_class($otherMyDateTime))) {
            return $this->getTime() == $otherMyDateTime->getTime();
        }

        return false;
    }

    public function isZero()
    {
        return $this->equals(self::valueOf(self::ZERO));
    }

    public static function getZero()
    {
        return self::valueOf(self::ZERO);
    }

    public function hashCode()
    {
        $time = $this->getTime();

        return intval($time ^ intval($time >> 32));
    }

    public static function printTime($time)
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        $alltime = floor((time() - $time) / 60);
        if ($alltime < 60) {
            if ($alltime <= 0) {
                $alltime = 1;
            }

            return $alltime.'����ǰ';
        } elseif ($alltime < 60 * 24) {
            return floor($alltime / 60).'Сʱǰ';
        } elseif ($alltime < 60 * 24 * 30) {
            return floor($alltime / 1440).'��ǰ';
        } else {
            return floor($alltime / 43200).'����ǰ';
        }
    }

    public static function printTime4www($time)
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        $alltime = floor((time() - $time) / 60);
        if ($alltime < 60) {
            if ($alltime <= 0) {
                $alltime = 1;
            }

            return $alltime.'����ǰ';
        } elseif ($alltime < 60 * 24) {
            return floor($alltime / 60).'Сʱǰ';
        } elseif ($alltime < 60 * 24 * 30) {
            return floor($alltime / 1440).'��ǰ';
        } else {
            return '1����ǰ';
        }
    }

    public static function printTime4Redesign($time)
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        $alltime = floor(time() - $time);
        $tmpTime = self::valueOfTime($time)->toStringByFormat(self::DEFAULT_DATE_FORMAT);
        if ($alltime < 60) {
            return '�ո�';
        } elseif ($alltime < 60 * 60) {
            return floor($alltime / 60).'����ǰ';
        } elseif (strtotime($tmpTime) == strtotime(self::today())) {
            return '����'.self::valueOfTime($time)->toStringByFormat('H:i');
        } elseif (strtotime($tmpTime) == strtotime(self::yesterday())) {
            return '����'.self::valueOfTime($time)->toStringByFormat('H:i');
        } elseif (self::yearDiff(self::valueOfTime($time), self::now())) {
            //��ǰ��ݴ����ύ���

            return self::valueOfTime($time)->toStringByFormat('Y.m.d H:i');
        } else {
            return self::valueOfTime($time)->toStringByFormat('m.d H:i');
        }
    }

    public static function dayAfterTomorrow($format = self::DEFAULT_DATE_FORMAT)
    {
        return new self(date($format, time() + 3600 * 24 * 2));
    }

    public static function printTime4Touch($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        $alltime = floor((time() - $time) / 60);
        if ($alltime < 60) {
            if ($alltime <= 0) {
                $alltime = 1;
            }

            return $alltime.'����ǰ';
        } elseif ($alltime < 60 * 24) {
            return floor($alltime / 60).'Сʱǰ';
        } elseif ($alltime < 60 * 24 * 7) {
            return floor($alltime / (60 * 24)).'��ǰ';
        } else {
            return self::valueOfTime($time)->toStringByFormat($format);
        }
    }

    public static function getDateDiffDesc(MyDateTime $fromTime, MyDateTime $endTime)
    {
        $diff = self::valueOf($fromTime)->getDateDiff($endTime);
        $yearDiff = $diff['year'];
        $monthDiff = $diff['month'];
        $dayDiff = $diff['day'];
        if ($yearDiff == 0 && $monthDiff == 0) {
            return $dayDiff.'��';
        } elseif ($yearDiff == 0) {
            return ($dayDiff ? $monthDiff + 1 : $monthDiff).'����';
        } else {
            return $yearDiff.'��'.($monthDiff ? ($dayDiff ? $monthDiff + 1 : $monthDiff).'����' : '');
        }
    }

    public function getAge()
    {
        return (time() - ($this->getTime())) / (365 * self::SECOND_OF_DAY);
    }

    public static function dbTime()
    {
        list($usec, $sec) = explode(' ', microtime());

        return (float) $usec + (float) $sec;
    }

    public function isUnixStartTime()
    {
        return $this->getTime() == self::valueOf(self::DEFAULT_START_TIME)->getTime();
    }

    //ȡĳ�µ�һ������һ��
    public static function getTheMonth(MyDateTime $date)
    {
        $firstday = self::valueOf(date('Y-m-01', strtotime($date)));
        $lastday = self::valueOf(date('Y-m-d', strtotime("$firstday +1 month -1 day")));
        $firstFormatDay = $firstday->getYear().'��'.$firstday->getMonth().'��'.$firstday->getDay().'��';
        $lastFormatDay = $lastday->getYear().'��'.$lastday->getMonth().'��'.$lastday->getDay().'��';

        return array('first' => $firstFormatDay, 'last' => $lastFormatDay);
    }

    public static function isDate($str, $format = 'Y-m-d')
    {
        $strArr = explode('-', $str);
        if (empty($strArr)) {
            return false;
        }
        foreach ($strArr as $val) {
            if (strlen($val) < 2) {
                $val = '0'.$val;
            }
            $newArr[] = $val;
        }
        $str = implode('-', $newArr);
        $unixTime = strtotime($str);
        $checkDate = date($format, $unixTime);
        if ($checkDate == $str) {
            return true;
        } else {
            return false;
        }
    }

    public static function isDate4Ymdhis($str, $format = 'Y-m-d H:i:s')
    {
        $strArr = explode(' ', $str);
        if (empty($strArr)) {
            return false;
        }
        if (!self::isDate($strArr[0])) {
            return false;
        }
        $strArr2 = explode(':', $strArr[1]);
        if (empty($strArr2)) {
            return false;
        }
        if (count($strArr2) == 3) {
            return true;
        } else {
            return false;
        }
    }

    public function isMonday()
    {
        $index = (int) date('N', $this->getTime());

        return 1 == $index;
    }

    //�Ƿ�ڼ���
    public function isHoliday()
    {
        foreach (self::$holiday as $aHoliday) {
            if ($this->toShortString().' 00:00:00' >= $aHoliday['beginTime'] && $this->toShortString().' 00:00:00' <= $aHoliday['endTime']) {
                return true;
            }
        }

        return false;
    }

    public static function get10MinutesAgo()
    {
        return date('Y-m-d H:i:s', time() - (60 * 10));
    }

    //���������������
    public static function infinityDate()
    {
        return self::valueOf(self::FAKE_INFINITYDATE);
    }

    public static function calculateWorkingDay(MyDateTime $startDay, $dayNums)
    {
        DBC::requireTrue(is_numeric($dayNums) && $dayNums > 0, '��������Ϊ����');
        $tagNum = 0;
        for ($endDay = $startDay; $tagNum < $dayNums; $endDay = $endDay->addDay(1)) {
            if ($endDay->isHoliday() || false == $endDay->isWorkingDay()) {
                continue;
            }
            ++$tagNum;
        }

        return $endDay->addDay(-1);
    }

    public function addWorkingDay($dayNums)
    {
        DBC::requireTrue(is_numeric($dayNums) && $dayNums > 0, '��������Ϊ����');
        $tagNum = 0;
        for ($endDay = $this; $tagNum < $dayNums; $endDay = $endDay->addDay(1)) {
            if ($endDay->isHoliday() || false == $endDay->isWorkingDay()) {
                continue;
            }
            ++$tagNum;
        }
        while ($endDay->isHoliday() || false == $endDay->isWorkingDay()) {
            $endDay = $endDay->addDay(1);
        }

        return $endDay;
    }

    public function isWednesday()
    {
        return 3 == (int) date('N', $this->getTime());
    }

    public static function isSpringHoliday()
    {
        return date('Y-m-d H:i:s') >= '2014-02-15 12:00:00' && date('Y-m-d H:i:s') <= '2014-02-25 00:00:00';
    }

    //TODO:get out
    public static function birthday2Age($birthday)
    {
        $age = '';
        try {
            $diff = self::valueOf($birthday)->getDateDiff(self::now());
        } catch (Exception $ex) {
            return '';
        }

        $year = $diff['year'];
        $month = $diff['month'];
        $day = $diff['day'];

        if ($year >= 10) {
            $age = $year.'��';
        } else {
            if ($day > 1) {
                ++$month;
            }

            if ($year == 1 && $month == 0 && $day == 0) {
                $age = '12����';
            }
            if ($year < 10 && $year >= 1) {
                if (12 == $month) {
                    ++$year;
                    $month = 0;
                }
                $age = $year.'��'.($month > 0 ? ($month.'����') : '');
            } elseif ($year == 0 && $month > 1) {
                $age = $month.'����';
            } elseif ($year == 0 && $month = 1 && $day == 1) {
                $birthday = self::valueOf($birthday);
                if (self::now()->getMonth() != $birthday->getMonth()) {
                    $age = '1����';
                }
            } elseif ($year == 0 && $month = 1 && $day > 1) {
                $age = "{$day}��";
            } else {
                $age = '';
            }
        }

        return $age;
    }/*}}}*/

    public static function second2HourAndMinute($second)
    {
        $str = '';
        if ($second >= 3600) {
            $hour = floor($second / 3600);
            $second = $second % 3600;
            $str .= $hour.'Сʱ';
        }
        $minute = floor($second / 60);
        $second = $second % 60;
        $str .= $minute.'��';

        return $str;
    }

    //TODO:ҵ����
    public static function getHelloWord()
    {
        $helloArr = array('����', '����', '����', '����', '����');
        $timeArr = array(0, 0, 0, 0, 0, 0, 1, 1, 1, 2, 2, 3, 3, 4, 4, 4, 4, 4, 4, 0, 0, 0, 0, 0);

        return $helloArr[$timeArr[(int) date('H', time())]].'��';
    }

    public static function getSecondDiffDesc($first, $second)
    {
        $timeDiff = array('0second' => '');
        if ($second > $first) {
            $timeDiff ['0second'] = $second - $first;
        }
        $timeDiff ['1mintue'] = round($timeDiff ['0second'] / 60).'��';
        $timeDiff ['2hour'] = round($timeDiff ['1mintue'] / 60).'Сʱ';
        $timeDiff ['3day'] = round($timeDiff ['2hour'] / 24).'��';

        krsort($timeDiff);
        $strFinalTime = '';
        foreach ($timeDiff as $finalTime) {
            preg_match('/\d+/', $finalTime, $match);
            if (isset($match[0]) && (int) $match[0] > 0) {
                $strFinalTime = $finalTime;
                break;
            }
        }

        return $strFinalTime;
    }

    public static function getDateTimeDiffDesc(MyDateTime $first, MyDateTime $second)
    {
        return self::getSecondDiffDesc($first->getTime(), $second->getTime());
    }

    public static function getDiffInfo(MyDateTime $startDate, MyDateTime $endDate)
    {
        return self::seconds2DiffInfo(abs($startDate->getTime() - $endDate->getTime()));
    }

    private static function seconds2DiffInfo($seconds)
    {
        $days = intval($seconds / 86400);
        $remain = $seconds % 86400;
        $hours = intval($remain / 3600);
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        $secs = $remain % 60;
        $res = array('day' => $days, 'hour' => $hours, 'min' => $mins, 'sec' => $secs);

        return $res;
    }

    public static function getWeek($date)
    {
        $week = array(0 => '����', 1 => '��һ', 2 => '�ܶ�', 3 => '����', 4 => '����', 5 => '����', 6 => '����');
        $num = date('w', strtotime($date));

        return isset($week[$num]) ? $week[$num] : '';
    }

    public static function getMicrotFormatTime($microTime = '', $format = self::DEFAULT_MICROTIME_FORMAT)
    {
        if (empty($microTime)) {
            $microTime = microtime();
        }
        list($usec, $sec) = explode(' ', $microTime);
        $date = date($format, $sec);
        $numberTime = number_format((float) $usec * 1000, 0, '.', '');
        $realTime = str_pad($numberTime, 3, '0', STR_PAD_LEFT);

        return str_replace('x', $realTime, $date);
    }
}
