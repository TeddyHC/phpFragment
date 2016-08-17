<?php

class MyCalender
{
    const CAL_TABLE_CELLS_BIG = 42;
    const CAL_TABLE_CELLS_SMALL = 35;

    private static $monthDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    private static $weeks = ['0' => '星期日', '1' => '星期一', '2' => '星期二', '3' => '星期三', '4' => '星期四', '5' => '星期五', '6' => '星期六'];
    private $date;

    protected function __construct($date)
    {
        $this->date = $date;
    }

    public static function isLeapYear($year)
    {
        return ($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0;
    }

    public static function getDayNumOfMonth($year, $month)
    {
        $dayNum = self::$monthDays[$month - 1];
        if ($month == 2) {
            $dayNum += self::isRunYear($year) ? 1 : 0;
        }

        return $dayNum;
    }

    public static function getCalTable($year, $month)
    {
        $firstDate = XDateTime::createXDateTime($year, $month, 1);
        $firstDateIndex = (int) date('N', $firstDate->getTime());
        $firstDateIndex = $firstDateIndex - 1;
        $calTableCellNum = ($firstDateIndex > 2) ? self::CAL_TABLE_CELLS_BIG : self::CAL_TABLE_CELLS_SMALL;
        $tableCells = array();
        $dayNumber = self::getNumberOfDays($year, $month);

        $dayIndex = 0;
        for ($i = 0; $i < $calTableCellNum; ++$i) {
            if (($i >= $firstDateIndex && $i < $dayNumber) || ($i >= $dayNumber && $dayIndex < $dayNumber)) {
                $dayIndex += 1;
                $tableCells[] = $dayIndex;
            } else {
                $tableCells[] = '';
            }
        }

        return $tableCells;
    }
}
