<?php
class MyCalender
{
    const CAL_TABLE_CELLS_BIG = 42;
    const CAL_TABLE_CELLS_SMALL = 35;
    private static $bigMonths = array(1, 3, 5, 7, 8, 10, 12);
	private static $weeks = array("1"=>"����һ", "2"=>"���ڶ�", "3"=>"������", "4"=>"������", "5"=>"������", "6"=>"������", "7"=>"������");
	private $date;	//�����ַ��� 

	protected function __construct($date)
	{
		$this->date = $date;
	}

	public static function getNumberOfDays($year, $month)
    {
        $dayNumber = 30;
        if($month == 2)
            $dayNumber = (self::isRunYear($year)) ? 29 : 28;
        if(in_array($month, self::$bigMonths))
            $dayNumber = 31;
        return $dayNumber;
	}

    public static function isRunYear($year)
    {
        return ($year % 400 == 0 || ($year % 100 !=0 && $year % 4 == 0));
    }

	public static function getCalTable($year, $month)
	{
        $firstDate = XDateTime::createXDateTime($year, $month, 1);
    	$firstDateIndex = (int)date('N', $firstDate->getTime());
        $firstDateIndex = $firstDateIndex - 1;
        $calTableCellNum = ($firstDateIndex > 2) ? self::CAL_TABLE_CELLS_BIG : self::CAL_TABLE_CELLS_SMALL;
        $tableCells = array();
        $dayNumber = self::getNumberOfDays($year, $month);
        
        $dayIndex = 0;
        for($i=0; $i<$calTableCellNum; $i++)
        {
            if(($i >= $firstDateIndex && $i < $dayNumber) || ($i >= $dayNumber && $dayIndex < $dayNumber))
            {
                $dayIndex += 1;
                $tableCells[] = $dayIndex;
            }
            else
            {
                $tableCells[] = '';
            }
        }
        return $tableCells;
	}


}
