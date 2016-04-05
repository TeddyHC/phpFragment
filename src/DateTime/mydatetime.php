<?php
class XDateTimeException extends SystemException
{
    const FORMAT_XDATETIME_ERROR = "ת��ʱ������ʧ��";
}

class MyDateTime
{
    const DAY_SECOND = 86400;

    const DEFAULT_DATE_FORMAT = "Y-m-d";
    const DEFAULT_XDATETIME_FORMAT = "Y-m-d H:i:s";
    const DEFAULT_XDATETIME_SIMPLE_FORMAT = "y-m-d H:i";
    const DEFAULT_MICROTIME_FORMAT = "Y-m-d H:i:s:x";
    const DEFAULT_XDATETIME_SIMPLE_FORMAT_TIME = "Y-m-d H:i";
    const DEFAULT_START_TIME = "1970-01-01 00:00:00";   //Unixʱ��� ��ʼʱ��
    const DEFAULT_UNDEFINE_TIME = "0000-00-00 00:00:00";

    const ZERO = 0;
    const YEAR = "Y";
    const MONTH = "m";
    const DAY = "d";
    const HOUR = "H";
    const MINUTE = "i";
    const SECOND = "s";
    const INFINITY_DATE = "9999-01-01 00:00:00";

    private static $weeks = array("1"=>"����һ", "2"=>"���ڶ�", "3"=>"������", "4"=>"������", "5"=>"������", "6"=>"������", "7"=>"������");
    private static $workingDays = array("1"=>"����һ", "2"=>"���ڶ�", "3"=>"������", "4"=>"������", "5"=>"������");
    private static $nowForTest = null;
    private $date;  //�����ַ��� ��:"2008-09-01 08:00:00"

    //�����ڼ��ռӺ�ͣ��\���ʹ��
    //2016-01-04 by ycq,TZFRAGREQ-859 ���ڼ��ս���ʱ��ĳ�"23:59:59"
    public static $holiday = array(
        0=>array('beginTime' => '2016-01-01 00:00:00', 'endTime' => '2016-01-03 23:59:59'),
        1=>array('beginTime' => '2016-02-07 00:00:00', 'endTime' => '2016-02-13 23:59:59'),
        2=>array('beginTime' => '2016-04-02 00:00:00', 'endTime' => '2016-04-04 23:59:59'),
        3=>array('beginTime' => '2016-04-30 00:00:00', 'endTime' => '2016-05-02 23:59:59'),
        4=>array('beginTime' => '2016-06-09 00:00:00', 'endTime' => '2016-06-11 23:59:59'),
        5=>array('beginTime' => '2016-09-15 00:00:00', 'endTime' => '2016-09-17 23:59:59'),
        6=>array('beginTime' => '2016-10-01 00:00:00', 'endTime' => '2016-10-07 23:59:59')
    );


    protected function __construct($date)
    {/*{{{*/
        $this->date = $date;
    }/*}}}*/


    //���� �����ַ��� ******start
    public static function today($format = self::DEFAULT_DATE_FORMAT)
    {/*{{{*/
        return XDateTime::valueOf(date($format));
    }/*}}}*/


    public static function now($format = self::DEFAULT_XDATETIME_FORMAT)
    {/*{{{*/
        if(self::$nowForTest != null)
            return self::$nowForTest;
        return XDateTime::valueOf(date($format));
    }/*}}}*/


    //���� *********************end
    public static function createXDateTime($year, $month, $day, $hour="00", $minute="00", $second="00")
    {/*{{{*/
        $time = mktime($hour, $minute, $second, $month, $day, $year);
        if($time == false)
            throw new XDateTimeException(XDateTimeException::FORMAT_XDATETIME_ERROR);
        $date = date(self::DEFAULT_XDATETIME_FORMAT, $time);
        return new XDateTime($date);
    }/*}}}*/

    public static function valueOf($date)
    {/*{{{*/
        if ($date == self::DEFAULT_UNDEFINE_TIME || $date == '0000-00-00' || $date == '0') {
            return new XDateTime(0);
        }
        if(strtotime($date) === false)
            throw new XDateTimeException(XDateTimeException::FORMAT_XDATETIME_ERROR);
        return new XDateTime($date);
    }/*}}}*/

    public static function valueOfNow()
    {/*{{{*/
        return self::now();
    }/*}}}*/

    public static function valueOfTime($timestamp)
    {/*{{{*/
        //  DBC::requireNotEmptyString($timestamp, "ʱ�������Ϊ��");

        $date = date(self::DEFAULT_XDATETIME_FORMAT, $timestamp);
        return new XDateTime($date);
    }/*}}}*/

    public static function setNowForTest(XDateTime $now)
    {/*{{{*/
        self::$nowForTest = $now;
    }/*}}}*/

    public static function cleanNowForTest()
    {/*{{{*/
        self::$nowForTest = null;
    }/*}}}*/

    public static function getNowForTest()
    {/*{{{*/
        return self::$nowForTest;
    }/*}}}*/

    public static function setChinaTimeZone()
    {/*{{{*/
        date_default_timezone_set('Asia/Shanghai');
    }/*}}}*/

    /* deprecation start �����ԭ�������еĺ���, �����ʵ�����ƹ��� ����addDay() �� setDay()*/
    // ����
    public static function yesterday($format = self::DEFAULT_DATE_FORMAT)
    {/*{{{*/
        return new XDateTime(date($format, time() - 3600 * 24));
    }/*}}}*/

    // ǰ��
    public static function dayBeforeYesterday($format = self::DEFAULT_DATE_FORMAT)
    {/*{{{*/
        return new XDateTime(date($format, time() - 3600 * 24 * 2));
    }/*}}}*/

    public static function tomorrow($format = self::DEFAULT_DATE_FORMAT)
    {/*{{{*/
        return new XDateTime(date($format, time() + 3600 * 24));
    }/*}}}*/

    /* deprecation end*/
    public function before($xDateTime)
    {/*{{{*/
        return ($this->getTime() < $xDateTime->getTime());
    }/*}}}*/

    public function after($xDateTime)
    {/*{{{*/
        return ($this->getTime() > $xDateTime->getTime());
    }/*}}}*/

    public function getDate()
    {/*{{{*/
        return $this->date;
    }/*}}}*/

    public function getTime()
    {/*{{{*/
        return strtotime($this->date);
    }/*}}}*/

    public function addYear($year)
    {/*{{{*/
        $year = $this->getYear() + $year;
        $month = $this->getMonth();
        $day = $this->getDay();
        $hour = $this->getHour();
        $minute = $this->getMinute();
        $second = $this->getSecond();
        return self::createXDateTime($year, $month, $day, $hour, $minute, $second);
    }/*}}}*/

    public function addMonth($month)
    {/*{{{*/
        $year = $this->getYear();
        $month = $this->getMonth() + $month;
        $day = $this->getDay();
        $hour = $this->getHour();
        $minute = $this->getMinute();
        $second = $this->getSecond();
        return self::createXDateTime($year, $month, $day, $hour, $minute, $second);
    }/*}}}*/

    public function addDay($day)
    {/*{{{*/
        return $this->addSecond($day * (60 * 60 * 24));
    }/*}}}*/

    public function addHour($hour)
    {/*{{{*/
        return $this->addSecond($hour * (60 * 60));
    }/*}}}*/

    public function addMinute($minute)
    {/*{{{*/
        return $this->addSecond($minute * 60);
    }/*}}}*/

    public function addSecond($second)
    {/*{{{*/
        $time = $this->getTime() + $second;
        return self::valueOf(date(self::DEFAULT_XDATETIME_FORMAT, $time));
    }/*}}}*/

    public function setYear($year)
    {/*{{{*/
        return $this->setDate(self::YEAR, $year);
    }/*}}}*/

    public function setMonth($month)
    {/*{{{*/
        return $this->setDate(self::MONTH, $month);
    }/*}}}*/

    public function setDay($day)
    {/*{{{*/
        return $this->setDate(self::DAY, $day);
    }/*}}}*/

    public function setHour($hour)
    {/*{{{*/
        return $this->setDate(self::HOUR, $hour);
    }/*}}}*/

    public function setMinute($minute)
    {/*{{{*/
        return $this->setDate(self::MINUTE, $minute);
    }/*}}}*/

    public function setSecond($second)
    {/*{{{*/
        return $this->setDate(self::SECOND, $second);
    }/*}}}*/

    private function setDate($format, $setNum)
    {/*{{{*/
        $year = $this->getYear();
        $month = $this->getMonth();
        $day = $this->getDay();
        $hour = $this->getHour();
        $minute = $this->getMinute();
        $second = $this->getSecond();
        switch($format)
        {
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
        return self::createXDateTime($year, $month, $day, $hour, $minute, $second);
    }/*}}}*/

    public function getDateDiff(XDateTime $nowDate)
    {/*{{{*/
        $arrNow = getdate(strtotime($nowDate));
        $arrCurrent = getdate($this->getTime());

        $yearDiff = $arrNow['year'] - $arrCurrent['year'];
        $monthDiff = $arrNow['mon'] - $arrCurrent['mon'];
        $dayDiff = $arrNow['mday']+1 - $arrCurrent['mday'];
        //�������
        if($yearDiff > 0
            && $arrNow['mon'] == 2
            && $arrCurrent['mon'] == 2
            && $arrCurrent['mday'] >=28
            && $arrNow['mday'] >=28
        )
        {
            $current2Days = $this->getDaysOfMonth($arrCurrent['year'], $arrCurrent['mon']);
            $now2Days = $this->getDaysOfMonth($arrNow['year'], $arrNow['mon']);
            if($current2Days != $now2Days)
            {
                if($current2Days == 29)
                {
                    $dayDiff = 0;
                }
            }
        }

        if($dayDiff < 0)
        {
            $monthDiff--;
            $monthDays = $this->getDaysOfMonth($arrCurrent['year'], $arrCurrent['mon']);
            $dayDiff = $arrNow['mday'] + ($monthDays - $arrCurrent['mday']);
        }

        if($monthDiff < 0)
        {
            $yearDiff--;
            $monthDiff = 12 + $monthDiff;
        }
        return array('year'=>$yearDiff, 'month'=>$monthDiff, 'day'=>$dayDiff);
    }/*}}}*/

    public static function getDaysOfMonth($year, $month)
    {/*{{{*/
        $days = 0;
        switch ($month)
        {
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
                                    if (($year % 4 == 0 && $year % 100 != 0 ) || $year % 400 == 0)
                                    {
                                        $days = 29;
                                    }
                                    else
                                    {
                                        $days = 28;
                                    }
                                    break;
                                default:
                                    $days = 30;
                                    break;
        }
        return $days;
    }/*}}}*/

    public function getYear()
    {/*{{{*/
        return date("Y", $this->getTime());
    }/*}}}*/

    public function getMonth()
    {/*{{{*/
        return date("m", $this->getTime());
    }/*}}}*/

    public function getDay()
    {/*{{{*/
        return date("d", $this->getTime());
    }/*}}}*/

    public function getHour()
    {/*{{{*/
        return date("H", $this->getTime());
    }/*}}}*/

    public function getMinute()
    {/*{{{*/
        return date("i", $this->getTime());
    }/*}}}*/

    public function getSecond()
    {/*{{{*/
        return date("s", $this->getTime());
    }/*}}}*/

    public function getMonthAndDay()
    {/*{{{*/
        return date("m-d", $this->getTime());
    }/*}}}*/

    public function getDateTime()
    {/*{{{*/
        return date("Y-m-d", $this->getTime());
    }/*}}}*/

    public function getWeekDesc()
    {/*{{{*/
        $index = (int)date('N', $this->getTime());
        return self::$weeks["$index"];
    }/*}}}*/

    public function isWorkingDay()
    {/*{{{*/
        $index = (int)date('N', $this->getTime());
        return array_key_exists($index, self::$workingDays);
    }/*}}}*/

    public static function yearDiff(XDateTime $d1, XDateTime $d2)
    {/*{{{*/
        return ($d2->getYear() - $d1->getYear());
    }/*}}}*/

    public static function monthDiff(XDateTime $d1, XDateTime $d2)
    {/*{{{*/
        $diff = self::yearDiff($d1, $d2) * 12 + ($d2->getMonth() - $d1->getMonth());
        if ($d2->getDay() - $d1->getDay() < 0)
            $diff--;
        return $diff;
    }/*}}}*/

    public static function dayDiff(XDateTime $d1, XDateTime $d2)
    {/*{{{*/
        return floor(($d2->getTime() - $d1->getTime()) / (60 * 60 * 24));
    }/*}}}*/

    public static function hourDiff(XDateTime $d1, XDateTime $d2)
    {/*{{{*/
        return floor(($d2->getTime() - $d1->getTime()) / (60 * 60));
    }/*}}}*/

    public static function minuteDiff(XDateTime $d1, XDateTime $d2)
    {/*{{{*/
        return floor(($d2->getTime() - $d1->getTime()) / 60);
    }/*}}}*/

    public static function secondDiff(XDateTime $d1, XDateTime $d2)
    {/*{{{*/
        return floor(($d2->getTime() - $d1->getTime()));
    }/*}}}*/

    public static function addDaysFromNow($days)
    {/*{{{*/
        return new XDateTime(date(self::DEFAULT_XDATETIME_FORMAT, time()+(int)$days*86400));
    }/*}}}*/

    public function between($beginDate, $endDate)
    {/*{{{*/
        return $this->getTime() >= $beginDate->getTime() && $this->getTime() <= $endDate->getTime();
    }/*}}}*/

    public static function afterToday($date)
    {/*{{{*/
        if (empty($date) || ($date == ""))
        {
            return true;
        }
        if (is_string($date))
            $date = strtotime($date);
        return $date > time();
    }/*}}}*/

    public static function getYearsToToday($from = 1949)
    {/*{{{*/
        $years = array();
        $currentYear = (int)date('Y', time());
        for($year = $from; $year <= $currentYear; $year++)
        {
            $years[strval($year)] = strval($year);
        }
        return $years;
    }/*}}}*/

    public function toString()
    {/*{{{*/
        return $this->toStringByFormat();
    }/*}}}*/

    public function toShortString()
    {/*{{{*/
        return $this->toStringByFormat(self::DEFAULT_DATE_FORMAT);
    }/*}}}*/

    public function toStringByFormat($format="Y-m-d H:i:s")
    {/*{{{*/
        return date($format, $this->getTime());
    }/*}}}*/

    public function equals($otherXDateTime)
    {/*{{{*/
        if(is_object($otherXDateTime) && (get_class($this) == get_class($otherXDateTime)))
            return ($this->getTime() == $otherXDateTime->getTime());
        return false;
    }/*}}}*/

    public function isZero()
    {/*{{{*/
        return $this->equals(XDateTime::valueOf(self::ZERO));
    }/*}}}*/

    public static function getZero()
    {/*{{{*/
        return XDateTime::valueOf(self::ZERO);
    }/*}}}*/

    public function hashCode()
    {/*{{{*/
        $time = $this->getTime();
        return intval($time ^ intval($time >> 32));
    }/*}}}*/

    //@override
    public function __toString()
    {/*{{{*/
        return $this->toStringByFormat();
    }/*}}}*/

    public static function printTime($time)
    {/*{{{*/
        $time = is_numeric($time) ? $time : strtotime($time);
        $alltime = floor((time() - $time) / 60);
        if ($alltime < 60) {
            if ($alltime <= 0) $alltime = 1;
            return $alltime . '����ǰ';
        } elseif ($alltime < 60 * 24) {
            return floor($alltime / 60) . 'Сʱǰ';
        } elseif ($alltime < 60 * 24 * 30) {
            return floor($alltime / 1440) . '��ǰ';
        } else {
            return floor($alltime / 43200) . '����ǰ';
        }
    }/*}}}*/

    public static function printTime4www($time)
    {/*{{{*/
        $time = is_numeric($time) ? $time : strtotime($time);
        $alltime = floor((time() - $time) / 60);
        if ($alltime < 60) {
            if ($alltime <= 0) $alltime = 1;
            return $alltime . '����ǰ';
        } elseif ($alltime < 60 * 24) {
            return floor($alltime / 60) . 'Сʱǰ';
        } elseif ($alltime < 60 * 24 * 30) {
            return floor($alltime / 1440) . '��ǰ';
        } else {
            return '1����ǰ';
        }
    }/*}}}*/

    /** �İ�һ�� �ͻ���ʱ����ʾ��ͬ����
     *
     * @author ldy
     *
     * @Param @time ʱ��
     */
    public static function printTime4Redesign($time)
    {/*{{{*/
        $time = is_numeric($time) ? $time : strtotime($time);
        $alltime = floor(time() - $time);
        $tmpTime = XDateTime::valueOfTime($time)->toStringByFormat(self::DEFAULT_DATE_FORMAT);
        if($alltime < 60)
        {
            return '�ո�';
        }
        else if($alltime < 60 * 60)
        {
            return floor($alltime / 60).'����ǰ';
        }
        else if(strtotime($tmpTime) == strtotime(self::today()))
        {
            return '����'.XDateTime::valueOfTime($time)->toStringByFormat("H:i");
        }
        else if(strtotime($tmpTime) == strtotime(self::yesterday()))
        {
            return '����'.XDateTime::valueOfTime($time)->toStringByFormat("H:i");
        }
        else if(self::yearDiff(XDateTime::valueOfTime($time), XDateTime::now())) //��ǰ��ݴ����ύ���
        {
            return XDateTime::valueOfTime($time)->toStringByFormat("Y.m.d H:i");
        }
        else
        {
            return XDateTime::valueOfTime($time)->toStringByFormat("m.d H:i");
        }
    }/*}}}*/

    public static function printTime4MobileVip($time)
    {/*{{{*/
        $time = is_numeric($time) ? $time : strtotime($time);
        $tmpTime = XDateTime::valueOfTime($time)->toStringByFormat(self::DEFAULT_DATE_FORMAT);
        if(strtotime($tmpTime) == strtotime(self::today()))
        {
            return '����';
        }
        else if(strtotime($tmpTime) == strtotime(self::tomorrow()))
        {
            return '����';
        }
        else if(strtotime($tmpTime) == strtotime(self::dayAfterTomorrow()))
        {
            return '����';
        }
        else if(self::yearDiff(XDateTime::now(), XDateTime::valueOfTime($time)))
        {
            return XDateTime::valueOfTime($time)->toStringByFormat("Y.m.d");
        }
        else if(self::dayDiff(XDateTime::now(), XDateTime::valueOfTime($time)))
        {
            return XDateTime::valueOfTime($time)->toStringByFormat("m��d��");
        }
        else
        {
            return '��ʱû��ʱ���Լ';
        }
    }/*}}}*/

    public static function dayAfterTomorrow($format = self::DEFAULT_DATE_FORMAT)
    {/*{{{*/
        return new XDateTime(date($format, time() + 3600 * 24 * 2));
    }/*}}}*/

    public static function printTime4Touch($time, $format="Y-m-d H:i:s")
    {/*{{{*/
        $time = is_numeric($time) ? $time : strtotime($time);
        $alltime = floor((time() - $time) / 60);
        if ($alltime < 60) {
            if ($alltime <= 0) $alltime = 1;
            return $alltime . '����ǰ';
        } elseif ($alltime < 60 * 24) {
            return floor($alltime / 60) . 'Сʱǰ';
        } elseif ($alltime < 60 * 24 * 7) {
            return floor($alltime / (60 * 24)) . '��ǰ';
        } else {
            return XDateTime::valueOfTime($time)->toStringByFormat($format);
        }
    }/*}}}*/

    public static function printTime4TouchFixed(XDateTime $time)
    {/*{{{*/
        $diff = XDateTime::valueOf($time)->getDateDiff(XDateTime::now());
        $yearDiff = $diff['year'];
        $monthDiff = $diff['month'];
        $dayDiff = $diff['day'];
        if ($yearDiff > 0 || $monthDiff > 0) {
            return XDateTime::valueOf($time)->toShortString();
        } elseif ($dayDiff == 1) {
            return XDateTime::valueOf($time)->toStringByFormat("H:i");
        } elseif ($dayDiff == 2) {
            return '����';
        } elseif ($dayDiff == 3) {
            return 'ǰ��';
        } else {
            return XDateTime::valueOf($time)->toShortString();
        }
    }/*}}}*/

    public static function printTime4TouchFlow(XDateTime $time)
    {/*{{{*/
        $diff = XDateTime::valueOf($time)->getDateDiff(XDateTime::now());
        $monthDiff = $diff['month'];
        $dayDiff = $diff['day'];
        $time = XDateTime::valueOf($time);
        if ($time->getYear() <  self::now()->getYear()) {
            return '<div class="top">'.$time->getMonth().'��<span>'.$time->getDay().'</span>��</div><div class="bottom">'.$time->getYear().'��</div>';
        } elseif ($monthDiff > 0) {
            return '<div class="top">'.$time->getMonth().'��<span>'.$time->getDay().'</span>��</div>';
        } elseif ($dayDiff == 1) {
            return "����";
        } elseif ($dayDiff == 2) {
            return '����';
        } else {
            return '<div class="top">'.$time->getMonth().'��<span>'.$time->getDay().'</span>��</div>';
        }
    }/*}}}*/

    public static function getDateDiffDesc(XDateTime $fromTime, XDateTime $endTime)
    {/*{{{*/
        $diff = XDateTime::valueOf($fromTime)->getDateDiff($endTime);
        $yearDiff = $diff['year'];
        $monthDiff = $diff['month'];
        $dayDiff = $diff['day'];
        if ($yearDiff == 0 && $monthDiff == 0) {
            return $dayDiff.'��';
        } elseif ($yearDiff == 0) {
            return ($dayDiff ? $monthDiff+1 : $monthDiff).'����';
        } else {
            return $yearDiff.'��'.($monthDiff ? ($dayDiff ? $monthDiff+1 : $monthDiff).'����' : "");
        }
    }/*}}}*/

    public function getAge()
    {/*{{{*/
        return (time() - ($this->getTime())) / (365*24*3600);
    }/*}}}*/

    public static function dbTime()
    {/*{{{*/
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }/*}}}*/

    public function isUnixStartTime()
    {/*{{{*/
        return ($this->getTime() == XDateTime::valueOf(self::DEFAULT_START_TIME)->getTime());
    }/*}}}*/

    //ȡĳ�µ�һ������һ��
    public static function getTheMonth(XDateTime $date)
    {/*{{{*/
        $firstday = self::valueOf(date('Y-m-01', strtotime($date)));
        $lastday = self::valueOf(date('Y-m-d', strtotime("$firstday +1 month -1 day")));
        $firstFormatDay =  $firstday->getYear().'��'.$firstday->getMonth().'��'.$firstday->getDay().'��';
        $lastFormatDay =  $lastday->getYear().'��'.$lastday->getMonth().'��'.$lastday->getDay().'��';
        return array('first' => $firstFormatDay, 'last' => $lastFormatDay);
    }/*}}}*/

    public static function isDate($str, $format="Y-m-d")
    {/*{{{*/
        $strArr = explode("-",$str);
        if(empty($strArr)){
            return false;
        }
        foreach($strArr as $val)
        {
            if(strlen($val)<2)
            {
                $val="0".$val;
            }
            $newArr[]=$val;
        }
        $str =implode("-",$newArr);
        $unixTime=strtotime($str);
        $checkDate= date($format,$unixTime);
        if($checkDate==$str)
            return true;
        else
            return false;
    }/*}}}*/

    public static function isDate4Ymdhis($str, $format="Y-m-d H:i:s")
    {/*{{{*/
        $strArr = explode(" ", $str);
        if(empty($strArr)){
            return false;
        }
        if(!self::isDate($strArr[0]))
            return false;
        $strArr2=explode(":", $strArr[1]);
        if(empty($strArr2)){
            return false;
        }
        if(count($strArr2)==3)
            return true;
        else
            return false;
    } /*}}}*/

    public function isMonday()
    {/*{{{*/
        $index = (int)date('N', $this->getTime());
        return 1 == $index;
    }/*}}}*/

    //�Ƿ�ڼ���
    public function isHoliday()
    {/*{{{*/
        foreach (self::$holiday as $aHoliday)
        {
            if($this->toShortString().' 00:00:00' >= $aHoliday['beginTime'] && $this->toShortString().' 00:00:00' <= $aHoliday['endTime'])
            {
                return true;
            }
        }
        return false;
    }/*}}}*/

    public static function get10MinutesAgo()
    {/*{{{*/
        return date('Y-m-d H:i:s', time() - (60*10));
    }/*}}}*/

    //���������������
    public static function infinityDate()
    {/*{{{*/
        return XDateTime::valueOf(self::FAKE_INFINITYDATE);
    }/*}}}*/

    public static function calculateWorkingDay(XDateTime $startDay, $dayNums)
    {/*{{{*/
        DBC::requireTrue(is_numeric($dayNums) && $dayNums > 0, "��������Ϊ����");
        $tagNum = 0;
        for ($endDay = $startDay; $tagNum < $dayNums; $endDay = $endDay->addDay(1))
        {
            if ($endDay->isHoliday() || false == $endDay->isWorkingDay())
            {
                continue;
            }
            $tagNum ++;
        }
        return $endDay->addDay(-1);
    }/*}}}*/

    public  function addWorkingDay($dayNums)
    {/*{{{*/
        DBC::requireTrue(is_numeric($dayNums) && $dayNums > 0, "��������Ϊ����");
        $tagNum = 0;
        for ($endDay = $this; $tagNum < $dayNums; $endDay = $endDay->addDay(1))
        {
            if ($endDay->isHoliday() || false == $endDay->isWorkingDay())
            {
                continue;
            }
            $tagNum ++;
        }
        while ($endDay->isHoliday() || false == $endDay->isWorkingDay())
        {
            $endDay = $endDay->addDay(1);
        }
        return $endDay;
    }/*}}}*/

    public function isWednesday()
    {/*{{{*/
        return 3 == (int)date('N', $this->getTime());
    }/*}}}*/

    public static function isSpringHoliday()
    {/*{{{*/
        return date('Y-m-d H:i:s') >= '2014-02-15 12:00:00' && date('Y-m-d H:i:s') <= '2014-02-25 00:00:00';
    }/*}}}*/

    public static function isSpringHoliday4Case()
    {/*{{{*/
        return date('Y-m-d H:i:s') >= '2014-02-15 15:00:00' && date('Y-m-d H:i:s') <= '2014-02-22 22:00:00';
    }/*}}}*/

    public static function isSpringHoliday4Booking()
    {/*{{{*/
        return date('Y-m-d H:i:s') >= '2014-02-15 14:00:00' && date('Y-m-d H:i:s') <= '2014-02-24 09:30:00';
    }/*}}}*/

    public static function isSpringHoliday4Tel()
    {/*{{{*/
        return date('Y-m-d H:i:s') >= '2014-02-15 12:00:00' && date('Y-m-d H:i:s') <= '2014-02-25 00:00:00';
    }/*}}}*/

    public static function isClosedThreadTime()
    {
        return date('Y-m-d H:i:s') >= '2014-07-05 09:00:00' && date('Y-m-d H:i:s') <= '2014-07-05 12:00:00';
    }

    public static function birthday2Age($birthday)
    {/*{{{*/
        $age = '';
        try
        {
            $diff = XDateTime::valueOf($birthday)->getDateDiff(XDateTime::now());
        }
        catch (Exception $ex)
        {
            return '';
        }

        $year = $diff['year'];
        $month = $diff['month'];
        $day = $diff['day'];

        if($year>=10)
        {
            $age = $year."��";
        }
        else
        {
            if ($day>1)
            {
                $month++;
            }

            if($year == 1 && $month == 0 && $day == 0)
            {
                $age = "12����";
            }
            if ($year<10 && $year>=1)
            {
                if(12 == $month)
                {
                    $year++;
                    $month = 0;
                }
                $age = $year."��".($month>0?($month."����"):"");
            }
            else if($year == 0 && $month >1)
            {
                $age = $month."����";
            }
            else if($year == 0 && $month = 1 && $day == 1)
            {
                $birthday = XDateTime::valueOf($birthday);
                if(XDateTime::now()->getMonth() != $birthday->getMonth())
                {
                    $age = "1����";
                }
            }
            else if($year == 0 && $month = 1 && $day > 1)
            {
                $age = "{$day}��";
            }
            else
            {
                $age = "";
            }
        }
        return $age;
    }/*}}}*/

    public static function second2HourAndMinute($second)
    {/*{{{*/
        $str = '';
        if($second >= 3600)
        {
            $hour = floor($second/3600);
            $second = $second%3600;
            $str .= $hour.'Сʱ';
        }
        $minute = floor($second/60);
        $second = $second%60;
        $str .= $minute.'��';

        return $str;
    }/*}}}*/

    public static function getHelloWord()
    {/*{{{*/
        $helloArr = array('����', '����', '����', '����', '����');
        $timeArr = array(0,0,0,0,0,0,1,1,1,2,2,3,3,4,4,4,4,4,4,0,0,0,0,0);
        return $helloArr[$timeArr[(int)date('H', time())]]."��";
    }/*}}}*/

    public static function getSecondDiffDesc($first, $second)
    {
        $timeDiff = array('0second'=>'');
        if($second > $first)
        {
            $timeDiff ['0second'] = $second - $first;
        }
        $timeDiff ['1mintue'] = round ( $timeDiff ['0second'] / 60 )."��";
        $timeDiff ['2hour']   = round ( $timeDiff ['1mintue'] / 60 )."Сʱ";
        $timeDiff ['3day']    = round ( $timeDiff ['2hour'] / 24 )."��";

        krsort($timeDiff);
        $strFinalTime = "";
        foreach($timeDiff as $finalTime)
        {
            preg_match('/\d+/', $finalTime, $match);
            if (isset($match[0]) && (int)$match[0] > 0)
            {
                $strFinalTime = $finalTime;
                break;
            }
        }
        return $strFinalTime;
    }

    public static function getDateTimeDiffDesc(XDateTime $first, XDateTime $second)
    {
        return self::getSecondDiffDesc($first->getTime(), $second->getTime());
    }

    public static function getDiffInfo(XDateTime $startDate, XDateTime $endDate)
    {/*{{{*/
        return self::seconds2DiffInfo(abs($startDate->getTime() - $endDate->getTime()));
    }/*}}}*/

    /**
     * seconds2DiffInfo ������ת��Ϊ���켸Сʱ���ּ���
     *
     * @param mixed $seconds ����
     * @static
     * @access private
     */
    private static function seconds2DiffInfo($seconds)
    {/*{{{*/
        $days = intval($seconds/86400);
        $remain = $seconds%86400;
        $hours = intval($remain/3600);
        $remain = $remain%3600;
        $mins = intval($remain/60);
        $secs = $remain%60;
        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        return $res;
    }/*}}}*/

    public static function getWeek($date)
    {/*{{{*/
        $week = array(0=>'����', 1=>'��һ', 2=>'�ܶ�', 3=>'����', 4=>'����', 5=>'����', 6=>'����');
        $num = date('w',strtotime($date));
        return isset($week[$num]) ? $week[$num] : '';
    }/*}}}*/

    public static function getMicrotFormatTime($microTime='',$format = self::DEFAULT_MICROTIME_FORMAT)
    {
        if(empty($microTime)) $microTime = microtime();
        list($usec, $sec) = explode(" ", $microTime);
        $date = date($format, $sec);
        $numberTime = number_format((float)$usec*1000, 0, ".", "");
        $realTime = str_pad($numberTime, 3, "0", STR_PAD_LEFT);
        return str_replace('x', $realTime, $date);
    }
}
