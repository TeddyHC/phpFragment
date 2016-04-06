<?php
class XString
{
    const SEPARATOR_FOR_DIM_1 = "@_1_@";
    const SEPARATOR_FOR_DIM_2 = "@_2_@";

    public static function makeTimestamp($string){
        if(empty($string)) {
            // use "now":
            $time = time();
        } elseif (preg_match('/^\d{14}$/', $string)) {
            $time = mktime(substr($string, 8, 2),substr($string, 10, 2),substr($string, 12, 2),
                substr($string, 4, 2),substr($string, 6, 2),substr($string, 0, 4));
        } elseif (is_numeric($string)) {
            // it is a numeric string, we handle it as timestamp
            $time = (int)$string;
        } else {
            // strtotime should handle it
            $time = strtotime($string);
            if ($time == -1 || $time === false) {
                // strtotime() was not able to parse $string, use "now":
                $time = time();
            }
        }
        return $time;
    }

    /** 尝试把其他编码装换成utf8 */
    public static function convertToUnicode($str) {
        return self::convertToEncoding($str, 'UTF-8');
    }

    /** 尝试把其他编码装换成gbk */
    public static function convertToGbk($str) {
        //  return mb_convert_encoding($str, 'GBK', 'auto');
        return self::convertToEncoding($str, 'GBK');
    }

    private static function convertToEncoding($str, $toEncoding)
    {
        if ((! $str) || empty($str)) {
            return $str;
        }

        $maybechset = mb_detect_encoding($str, array('UTF-8',  'GBK', 'ASCII', 'EUC-CN',  'CP936', 'UCS-2'));
        if (empty($maybechset)) { // UCS-2编码无法识别，试图猜测是否是UCS-2编码
            $tmpstr = mb_convert_encoding($str, $toEncoding, 'UCS-2');
            $tmpchset = mb_detect_encoding($tmpstr, array('GBK'));
            if (strtoupper($tmpchset) == $toEncoding) {
                return $tmpstr;
            }
        } else if ($maybechset != $toEncoding) { // 不是 GBK，转换一下
            return mb_convert_encoding($str, $toEncoding, $maybechset);
        }
        return $str;
    }

    /** 尝试把其他编码装换成utf8 */
    public static function convertToUnicodeNew($str) {
        $encodingOrder = ['ASCII', 'CP936', 'GBK', 'UTF-8', 'EUC-CN', 'UCS-2'];
        return self::convertToEncodingNew($str, 'UTF-8', $encodingOrder);
    }

    /** 尝试把其他编码装换成gbk */
    public static function convertToGbkNew($str)
    {
        $encodingOrder = ['UTF-8', 'ASCII', 'CP936', 'GBK', 'EUC-CN', 'UCS-2'];
        return self::convertToEncodingNew($str, 'GBK', $encodingOrder);
    }

    private static function convertToEncodingNew($str, $toEncoding, $recognitionArr = NULL)
    {
        if ((! $str) || empty($str)) {
            return $str;
        }

        $encodingRecArr = ($recognitionArr === NULL) ? ['GBK', 'UTF-8'] : $recognitionArr;
        $maybechset = mb_detect_encoding($str, $encodingRecArr);
        if (empty($maybechset)) { // UCS-2编码无法识别，试图猜测是否是UCS-2编码
            $tmpstr = mb_convert_encoding($str, $toEncoding, 'UCS-2');
            $tmpchset = mb_detect_encoding($tmpstr, array('GBK'));
            if (strtoupper($tmpchset) == $toEncoding) {
                return $tmpstr;
            }
        } else if ($maybechset != $toEncoding) { // 不是 GBK，转换一下
            return mb_convert_encoding($str, $toEncoding, $maybechset);
        }
        return $str;
    }

    public static function truncate($string, $length, $postfix = '...')
    {
        $n = 0;
        $return = '';
        $isCode = false;	//是否是 HTML 代码
        $isHTML = false;	//是否是 HTML 特殊字符, 如&nbsp;
        for ($i = 0; $i < strlen($string); $i++) {
            $tmp1 = $string[$i];
            $tmp2 = ($i + 1 == strlen($string)) ? '' : $string[$i + 1];
            if ($tmp1 == '<') {
                $isCode = true;
            } elseif ($tmp1 == '&' && !$isCode) {
                $isHTML = true;
            } elseif ($tmp1 == '>' && $isCode) {
                $n--;
                $isCode = false;
            } elseif ($tmp1 == ';' && $isHTML) {
                $isHTML = false;
            }
            if (!$isCode && !$isHTML) {
                $n++;
                if (ord($tmp1) >= hexdec("0x81") && ord($tmp2) >= hexdec("0x40")) {
                    $tmp1 .= $tmp2;
                    $i++;
                    $n++;
                }
            }
            $return .= $tmp1;
            if ($n >= $length) {
                break;
            }
        }
        if ($n >= $length) {
            $return .= $postfix;
        }
        //取出截取字符串中的 HTML 标记
        $html = preg_replace('/(^|>)[^<>]*(<?)/', '$1$2', $return);
        //去掉不需要结束标记的 HTML 标记, 可根据情况自行更改
        $html = preg_replace("/<\/?(br|hr|img|input|param)[^<>]*\/?>/i", '', $html);
        //去掉成对的 HTML 标记
        $html = preg_replace('/<([a-zA-Z0-9]+)[^<>]*>.*?<\/\1>/', '', $html);
        //用正则表达式取出 HTML 标记
        $count = preg_match_all('/<([a-zA-Z0-9]+)[^<>]*>/', $html, $matches);
        //补全不成对的 HTML 标记
        for ($i = $count - 1; $i >= 0; $i--) {
            $return .= '</' . $matches[1][$i] . '>';
        }
        return $return;
    }

    public static function getMaskIp($ip){
        if ($pos = strrpos($ip, ".")) {
            $ip = substr($ip, 0,$pos).".*";
        }
        return $ip;
    }

    //TODO:hognchao  to static
    public static function highlight($text, $keyword, $color="red"){
        return str_replace($keyword, "<font color='$color'>$keyword</font>", $text);
    }

    //TODO:hongchao 没有调用点
    //修正 Word 文档格式为 HTML
    public function fixWordText($content) {
        $content = preg_replace("/<\/?\?xml[^>]*>/si", '', $content);
        $content = preg_replace("/<\/?o:p[^>]*>/si", '', $content);
        $content = preg_replace("/<\/?v:[^>]*>/si", '', $content);
        $content = preg_replace("/<\/?o:[^>]*>/si", '', $content);
        $content = preg_replace("/<\/?st1:[^>]*>/si", '', $content);
        $content = preg_replace("/<\/?w:wrap[^>]*>/si", '', $content);
        $content = preg_replace("/<\/?w:anchorlock[^>]*>/si", '', $content);
        $content = preg_replace("/<span\s+[^>]*mso[^>]*>/si", '<span>', $content);
        $content = preg_replace("/<p\s+[^>]*mso[^>]*>/si", '<p>', $content);
        $content = preg_replace("/<\/?P[^>]*><\/P>/si", '', $content);
        $content = preg_replace("/<\/?SPAN[^>]*><\/SPAN>/si", '', $content);
        $content = str_replace('</nbsp;>', '', $content);
        return $content;
    }

    public static function fixHtmlText($content){
        return str_replace(array(' ', "\r", "\n"), '', strip_tags($content));
    }

    public static function fixHtmlArray($arr, $convertKey=false)
    {
        if (empty($arr))
        {
            return $arr;
        }
        if (is_array($arr))
        {
            $res = array();
            foreach ($arr as $key => $value)
            {
                if ($convertKey)
                {
                    $key = self::cntrim($key);
                }
                if (is_array($value))
                {
                    $value = self::fixHtmlArray($value, $convertKey);
                }
                else
                {
                    self::cntrim($value);
                }
                $res[$key] = $value;
            }
        }
        else
        {
            $res = self::cntrim($arr);
        }
        return $res;
    }

    public static function cntrim($string){
        return trim($string, "　\t\n\r ");
    }

    public static function convertEncoding($arr, $toEncoding, $fromEncoding='', $convertKey=false)
    {
        if (empty($arr) || $toEncoding == $fromEncoding) {
            return $arr;
        }
        if (is_array($arr)) {
            $res = array();
            foreach ($arr as $key => $value) {
                if ($convertKey) {
                    $key = mb_convert_encoding($key, $toEncoding, $fromEncoding);
                }
                if (is_array($value)) {
                    $value = self::convertEncoding($value, $toEncoding, $fromEncoding, $convertKey);
                } else {
                    $value = mb_convert_encoding($value, $toEncoding, $fromEncoding);
                }
                $res[$key] = $value;
            }
        } else {
            $res = mb_convert_encoding($arr, $toEncoding, $fromEncoding);
        }
        return $res;
    }

    public static function printTime($time) {
        $time = is_numeric($time) ? $time : strtotime($time);
        $alltime = floor((time() - $time) / 60);
        if ($alltime < 60) {
            if ($alltime <= 0) $alltime = 1;
            return $alltime . '分钟前';
        } elseif ($alltime < 60 * 24) {
            return floor($alltime / 60) . '小时前';
        } elseif ($alltime < 60 * 24 * 30) {
            return floor($alltime / 1440) . '天前';
        } else {
            return floor($alltime / 43200) . '个月前';
        }
    }

    public static function getRandom($len)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
        shuffle($chars);// 将数组打乱
        $output = "";
        for ($i=0; $i<$len; $i++)
        {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    public static function toGbkDeep($value){
        return is_array($value) ? array_map('XString::toGbkDeep', $value) : (XString::convertToGbk($value));
    }

    //TODO:get out
    public static function xmlToArray($xml)
    {
        $charType = mb_detect_encoding($xml, array('ASCII', 'EUC-CN', 'UTF-8', 'CP936', 'UCS-2'));
        $xml = iconv($charType, $charType.'//ignore', $xml);
        $data = simplexml_load_string($xml,NULL,LIBXML_NOCDATA);
        return self::_simplexmlListToArray($data);
    }

    public static function array2XML($array, $charset = 'gbk', $needCdata=true, $surRound = 'DOCUMENT')
    {
        $header = "<?xml version='1.0' encoding='".$charset."' ?>\n";
        $body = self::array2XMLBody($array, $needCdata);
        if (false == empty($surRound))
        {
            $body = "<".$surRound.">\n".$body."\n</".$surRound.">";
        }
        return $header.$body;
    }

    public static function array2XMLBody($array, $needCdata=true)
    {
        if(false == is_array($array))
        {
            return array();
        }
        $xml = "";
        foreach($array as $key=>$val)
        {
            if(is_numeric($key))
            {
                foreach( $val as $key2 => $value)
                {
                    if (false == is_numeric($key2))
                    {
                        $xml.="<$key2>";
                    }
                    if ($needCdata)
                    {
                        $xml .= is_array($value)?self::array2XMLBody($value, $needCdata):'<![CDATA['.$value.']]>'."\n";
                    }
                    else
                    {
                        $xml .= is_array($value)?self::array2XMLBody($value, $needCdata):$value."\n";
                    }
                    if (false == is_numeric($key2))
                    {
                        list($key2,)=explode(' ',$key2);
                        $xml.="</$key2>\n";
                    }
                }
            }
            else
            {
                $pre = "<$key>";
                if (is_array($val) && isset($val['@attributes']) && is_array($val['@attributes']) && false == empty($val['@attributes']))
                {
                    $pre = "<$key";
                    foreach ($val['@attributes'] as $attributeName => $attributeValue)
                    {
                        $pre .= " $attributeName='$attributeValue' ";
                    }
                    $pre .= "/>";
                    unset($val['@attributes']);
                    $key = '';
                }
                $xml.=$pre;
                if ($needCdata)
                {
                    $xml.=is_array($val)?self::array2XMLBody($val, $needCdata):'<![CDATA['.$val.']]>';
                }
                else
                {
                    $xml.=is_array($val)?self::array2XMLBody($val, $needCdata):$val;
                }
                if ($key)
                {
                    list($key,)=explode(' ',$key);
                    $xml.="</$key>\n";
                }
            }
        }

        return $xml;
    }

    public static function sortArray(&$array, $type, $desc = true)
    {
        if(false == empty($array)) {
            foreach($array as $r) {
                if(is_array($r))
                {
                    $t[] = $r[$type];
                }
                elseif (is_object($r))
                {
                    $t[] = $r->$type;
                }
            }
            if($desc)
            {
                array_multisort($t, SORT_DESC,  $array);
            }
            else
            {
                array_multisort($t, SORT_ASC,  $array);
            }
        }
    }

    private static function checkListType($dataList)
    {
        if(is_array($dataList) && false == empty($dataList))
        {
            $before = '';
            $status = 0;
            foreach($dataList as $key => $data)
            {
                if(false == is_numeric($key))
                    return false;
                elseif(is_numeric($key) && is_array($data))
                {
                    if($status == 0)
                    {
                        $before = $data;
                        $status = 1;
                    }
                    if(array_diff_key($before, $data) || array_diff_key($data, $before))
                        return false;
                }
                elseif(is_numeric($key) && is_object($data))
                {
                    if($status == 0)
                    {
                        $before = get_class($data);
                        $status = 1;
                    }
                    if($before != get_class($data))
                        return false;
                }
                elseif(is_numeric($key) && false == is_array($data) && false == is_object($data) && is_array($before))
                    return false;
            }
            return true;
        }
        else
            return false;
    }

    private static function _simplexmlListToArray($data)
    {
        $tmp = array();
        if(self::checkListType($data))
        {
            foreach($data as $value)
                $tmp[] = self::_simplexmlToArray($value);
        }
        else
            $tmp = self::_simplexmlToArray($data);
        return $tmp;
    }

    private static function _simplexmlToArray($simplexml)
    {
        if(is_object($simplexml))
        {
            $simplexml = (array)$simplexml;
            if(false == empty($simplexml))
            {
                $data = array();
                foreach($simplexml as $key => $value)
                    $data[$key] = self::_simplexmlListToArray($value);
                return $data;
            }
            else
                return '';
        }
        else
            return $simplexml;
    }

    public static function convertCharacterSet($str, $toEncoding)
    {
        $charType = mb_detect_encoding($str, array('ASCII', 'EUC-CN', 'UTF-8', 'CP936', 'UCS-2'));
        return mb_convert_encoding($str, $toEncoding, $charType);
    }

    public static function isEmail($email)
    {
        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function isMobile($mobile)
    {
        $mobile = trim($mobile);
        if (preg_match('/^1[34578][0-9]{9}$/', $mobile))
            return self::isValidMobile($mobile);
        return false;
    }

    public static function isMobileNew($mobile)
    {
        $mobile = trim($mobile);
        if (preg_match('/^1[34578][0-9]{9}$/', $mobile))
        {
            return self::isValidMobile($mobile);
        }
        return false;
    }

    public static function isValidMobile($mobile)
    {
        if(substr($mobile, 0, 10) == '1380013800')
            return false;
        $str = substr($mobile, 2,9);
        for($i=0;$i<10;$i++)
        {
            if(substr_count($str,$i) == 9)
                return false;
        }
        return true;
    }

    public static function isNonlocalMobile($mobile)
    {
        return preg_match('/^01[34578][0-9]{9}$/', $mobile);
    }

    public static function isBeijingPhone($phone)
    {
        return preg_match('/^010[0-9]{8}$/', $phone) || preg_match('/^[^0][0-9]{7}$/', $phone);
    }

    public static function isNonlocalPhone($phone)
    {
        return preg_match('/^0[^1][0-9]{8,10}$/', $phone);
    }

    public static function isValidPhoneNumber($phone)
    {
        return self::isMobile($phone)
            || self::isNonlocalMobile($phone)
            || self::isBeijingPhone($phone)
            || self::isNonlocalPhone($phone);
    }

    public static function isPhone($phone)
    {
        return preg_match('/[0-9]{7}/', $phone);
    }

    public static function listFile($dirpath, $needPath = true)
    {
        if($dirpath[strlen($dirpath)-1]!='/'){
            $dirpath.="/";
        }
        $result_array=array();
        if(is_dir($dirpath)){
            $files_dirs=scandir($dirpath);
            foreach($files_dirs as $file)
            {
                if($file=='.'||$file=='..' || is_dir($dirpath.$file))
                {
                    continue;
                }
                else
                {
                    if ($needPath)
                    {
                        array_push($result_array, $dirpath.$file);
                    }
                    else
                    {
                        array_push($result_array, $file);
                    }
                }
            }
        }
        return $result_array;
    }

    public static function listDir($dirpath)
    {
        $result_array = array();
        if(is_dir($dirpath)){
            $files_dirs=scandir($dirpath);
            foreach($files_dirs as $file)
            {
                if($file <> '.' && $file <> '..')
                {
                    $result_array[] = $file;
                }
            }
        }
        return $result_array;
    }

    public static function clearDir($dirpath)
    {
        try
        {
            $fileList = self::listFile($dirpath);
            foreach ($fileList as $filePath)
            {
                if ($filePath <> "/.autofsck"  && $filePath<>"/.autorelabel")
                {
                    unlink($filePath);
                }
            }
        }
        catch(Exception $ex)
        {
            echo "无法正常删除文件，请查看权限！";
        }
    }

    public static function findPhoneNO($mixedString)
    {
        $mobilePhonePattern = "/01[1-9]\d{9}|1[1-9]\d{9}/";
        $fixedLinePattern1 = "/(\d{3,4})-(\d{7,8})/";
        $fixedLinePattern2 = "/\d{7,12}/";
        $matchCount = preg_match($mobilePhonePattern, $mixedString, $matches);
        if($matchCount > 0)
        {
            return $matches[0];
        }
        $matchCount = preg_match($fixedLinePattern1, $mixedString, $matches);
        if($matchCount > 0)
        {
            return $matches[1].$matches[2];
        }
        $matchCount = preg_match($fixedLinePattern2, $mixedString, $matches);
        if($matchCount > 0)
        {
            return $matches[0];
        }
        return false;
    }

    public static function generateFakePhoneno($phoneno)
    {
        $fakePhoneno = '';
        if($phoneno)
        {
            $fakePhoneno = $phoneno{0}.$phoneno{1}.$phoneno{2}.$phoneno{3}.$phoneno{4};
        }
        for( $amount=strlen($phoneno)-5; $amount>0; $amount--)
        {
            $fakePhoneno .= "*";
        }
        return $fakePhoneno;
    }

    public static function decodeMobileNo($mobile)
    {
        if(false == is_numeric($mobile))
        {
            $codec = Codec::getInstance();
            $mobile = $codec->decodeId($mobile);
        }
        return $mobile;
    }

    public static function encodeMobileNo($mobile)
    {
        if(is_numeric($mobile))
        {
            $codec = Codec::getInstance();
            $mobile = $codec->encodeId($mobile);
        }
        return $mobile;
    }

    public static function trimArray($array)
    {
        if($array && is_array($array))
        {
            foreach($array as $key => $value)
            {
                $array[$key] = self::trimArray($value);
                if(empty($array[$key])) unset($array[$key]);
            }
            return $array;
        }
        return trim($array);
    }

    public static function dumpArray($array, $root=0)
    {
        if(is_array($array))
        {
            $root += 1;
            $res = "array(\n";
            foreach($array as $key => $value)
            {
                $value = self::dumpArray($value, $root);
                $res .= "'$key' => $value\n";
            }
            if($root == 1) $res .= ");";
            else $res .= "),";
        }
        else
        {
            $res = "'$array',";
        }
        return $res;
    }

    public static function write2file($data, $file)
    {
        $fileInfo = pathInfo($file);
        if (false == is_dir($fileInfo['dirname']))
        {
            $oldumask=umask(0);
            mkdir($fileInfo['dirname'], 0755);
            umask($oldumask);
        }

        if(empty($data))
        {
            return false;
        }

        file_put_contents($file,$data);
        return true;
    }

    public static function urlencode_rfc3986($input)
    {
        if(is_array($input))
        {
            return array_map( array( __CLASS__ , 'urlencode_rfc3986') , $input);
        }
        else if(is_scalar($input))
        {
            return str_replace('%7E', '~', rawurlencode($input));
        }
        else
        {
            return '';
        }
    }

    public static function hash_hmac( $algo , $data , $key , $raw_output = false )
    {
        if(function_exists('hash_hmac'))
        {
            return hash_hmac($algo, $data, $key, $raw_output);
        }

        $algo = strtolower($algo);
        if($algo == 'sha1')
        {
            $pack = 'H40';
        }
        elseif($algo == 'md5')
        {
            $pach = 'H32';
        }
        else
        {
            return '';
        }
        $size = 64;
        $opad = str_repeat(chr(0x5C), $size);
        $ipad = str_repeat(chr(0x36), $size);

        if (strlen($key) > $size) {
            $key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
        } else {
            $key = str_pad($key, $size, chr(0x00));
        }

        for ($i = 0; $i < strlen($key) - 1; $i++) {
            $opad[$i] = $opad[$i] ^ $key[$i];
            $ipad[$i] = $ipad[$i] ^ $key[$i];
        }

        $output = $algo($opad.pack($pack, $algo($ipad.$data)));

        return ($raw_output) ? pack($pack, $output) : $output;
    }

    public static function replaceString($str)
    {
        $maskchar = array(chr(0), chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8), chr(9), chr(10), chr(11),
            chr(12), chr(13), chr(14), chr(15), chr(16), chr(17), chr(18), chr(19), chr(20), chr(21), chr(22), chr(23), chr(24),
            chr(25), chr(26), chr(27), chr(28) , chr(29), chr(30), chr(31));
        return str_replace($maskchar, "", $str);
    }

    public static function getDataFormatSN($length=13)
    {
        $base = 10;
        if($length <= 4)
        {
            $min = pow($base, $length - 1);
            $max = pow($base, $length);
            return mt_rand($min, $max);
        }else{
            $min = pow($base, $length -4 - 1);
            $max = pow($base, $length -4);
            return date('md', time()).mt_rand($min, $max);
        }
    }
    public static function getContentWithOutHtml($content)
    {
        $content = XString::convertToUnicode($content);
        $content =  html_entity_decode($content);
        $content = preg_replace("/\&ldquo\;/i", '"', $content);
        $content = preg_replace("/\&rdquo\;/i", '"', $content);
        //error_log($content."\n",3,'/tmp/wap123.log');
        //$content = strip_tags($content,'<br/> </p> </div> <br>');
        $params = array('<br />', '</p>', '</div>', '<br>','<BR>', '<BR />', '<div>');
        $content = str_replace($params, "\r\n", $content);
        $content = preg_replace("/<div[^>]*>/i", "\r\n", $content);
        $content = preg_replace("/<a[^>]*>/i", "", $content);
        $content = preg_replace("/&#\d{2,6};/", "", $content);
        $options = array('&nbsp;','&amp;nbsp;');
        $content = str_replace($options, " ", $content);
        //这会丢失文本
        $content = strip_tags($content);
        //error_log($content."\n",3,'/tmp/wap123.log');
        $content = preg_replace('/((\r\n )|(\r\n))+/', "\n", $content);
        $content = preg_replace('/((\n )|(\n))+/', "\n", $content);
        $content = XString::convertToGbk($content);
        if(mb_strlen($content) > 6746)
        {
            $content = substr($content, 0, 6746).'...';
        }
        return $content;
    }

    public static function htmlspecialchars_decode($str)
    {
        $str = str_replace("&#34;","\"",$str); //解决乱码问题
        $str = str_replace("&#39;","'",$str); //解决乱码问题
        $str = str_replace("&#62;",">",$str); //解决乱码问题
        $str = str_replace("&#60;","<",$str); //解决乱码问题
        $str = str_replace("&lt;","<",$str); //解决乱码问题
        $str = str_replace("&gt;",">",$str); //解决乱码问题
        $str = str_replace("&amp;","&",$str); //解决乱码问题
        $str = str_replace("&#039;","'",$str); //解决乱码问题
        $str = str_replace("&quot;","\"",$str); //解决乱码问题
        return $str;
    }

    public static function htmlspecialchars_encode($str)
    {
        $str = str_replace("\"","&#34;",$str); //解决乱码问题
        $str = str_replace("'","&#39;",$str); //解决乱码问题
        $str = str_replace(">","&#62;",$str); //解决乱码问题
        $str = str_replace("<","&lt;",$str); //解决乱码问题
        $str = str_replace(">","&gt;",$str); //解决乱码问题
        $str = str_replace("&","&amp;",$str); //解决乱码问题
        $str = str_replace("'","&#039;",$str); //解决乱码问题
        $str = str_replace("\"","&quot;",$str); //解决乱码问题
        return $str;
    }

    public static function setSearchText()
    {
        return '9';
    }

    //版本号比较 $v1:新版本号,$v2:旧版本号 返回boolean
    public static function versionCompare($v1, $v2)
    {
        if(empty($v1))
        {
            return FALSE;
        }
        $l1  = explode('.',$v1);
        $l2  = explode('.',$v2);
        $len = count($l1) < count($l2) ? count($l1): count($l2);
        for ($i = 0; $i < $len; $i++)
        {
            $n1 = $l1[$i];
            $n2 = $l2[$i];
            if ($n1 > $n2)
            {
                return TRUE;
            }
            else if ($n1 < $n2)
            {
                return FALSE;
            }
        }
        if (count($l1) > count($l2)) {
            return true;
        }
        return FALSE;

    }

    public static function arrayKeys($array, $key)
    {
        $keys = array();
        if(false == empty($array))
        {
            foreach($array as $a)
            {
                if(is_array($a))
                {
                    $keys[] = $a[$key];
                }
                elseif (is_object($a))
                {
                    $keys[] = $a->$key;
                }
            }
        }
        return $keys;
    }

    public static function isMatchDateFormat($dateStr)
    {
        $match = '((^((1[8-9]\d{2})|([2-9]\d{3}))([-\/\._])(10|12|0?[13578])([-\/\._])(3[01]|[12][0-9]|0?[1-9])$)|(^((1[8-9]\d{2})|([2-9]\d{3}))([-\/\._])(11|0?[469])([-\/\._])(30|[12][0-9]|0?[1-9])$)|(^((1[8-9]\d{2})|([2-9]\d{3}))([-\/\._])(0?2)([-\/\._])(2[0-8]|1[0-9]|0?[1-9])$)|(^([2468][048]00)([-\/\._])(0?2)([-\/\._])(29)$)|(^([3579][26]00)([-\/\._])(0?2)([-\/\._])(29)$)|(^([1][89][0][48])([-\/\._])(0?2)([-\/\._])(29)$)|(^([2-9][0-9][0][48])([-\/\._])(0?2)([-\/\._])(29)$)|(^([1][89][2468][048])([-\/\._])(0?2)([-\/\._])(29)$)|(^([2-9][0-9][2468][048])([-\/\._])(0?2)([-\/\._])(29)$)|(^([1][89][13579][26])([-\/\._])(0?2)([-\/\._])(29)$)|(^([2-9][0-9][13579][26])([-\/\._])(0?2)([-\/\._])(29)$))';
        return preg_match($match, $dateStr);
    }

    public static function jsArrayIsEmpty($jsArrayStr)
    {
        $arr = json_decode($jsArrayStr);
        return empty($arr);
    }

    public static function twoDimArray2String(Array $arr)
    {
        foreach($arr as $key => $a)
        {
            DBC::requireTrue(is_array($a), '只能转换2维数组');
            $b = implode(self::SEPARATOR_FOR_DIM_1, $a);
            $arr[$key] = $b;
        }

        return implode(self::SEPARATOR_FOR_DIM_2, $arr);
    }

    public static function string2TwoDimArray($str)
    {
        $str = html_entity_decode($str);
        $arr = explode(self::SEPARATOR_FOR_DIM_2, $str);
        foreach($arr as $key => $a)
        {
            $b = explode(self::SEPARATOR_FOR_DIM_1, $a);
            $arr[$key] = $b;
        }

        return $arr;
    }

    //去除前后空格后将空格变nbsp
    public static function changeBlank2nbspAndLtrim($str)
    {
        $newStr = rtrim(ltrim($str));
        return $newStr;
    }

    //全角转半角
    public static function fixContent2Banjiao($str)
    {
        $arr = array(
            'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
            'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
            'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
            'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
            'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
            'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
            'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
            'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
            'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
            'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
            'ｙ' => 'y', 'ｚ' => 'z', '０' => '0', '１' => '1', '２' => '2',
            '３' => '3', '４' => '4', '５' => '5', '６' => '6', '７' => '7',
            '８' => '8', '９' => '9', '　' => ' '
        );

        foreach($arr as $key => $value)
        {
            $str = mb_ereg_replace($key, $value, $str);
        }
        return $str;
    }

    public static function delAllSpace($str)
    {
        return str_replace(array(
            " ","\n", "　　",
        ), '', $str);
    }

    public static function getLengthOfGBKString($str)
    {
        $str = mb_convert_encoding($str, 'gbk', 'auto');
        return mb_strlen(XString::cntrim($str));
    }

    //反解js的escape
    public static function js_unescape($str)
    {
        $ret = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++)
        {
            if ($str[$i] == '%' && $str[$i+1] == 'u')
            {
                $val = hexdec(substr($str, $i+2, 4));
                if ($val < 0x7f) $ret .= chr($val);
                else if($val < 0x800) $ret .= chr(0xc0|($val>>6)).chr(0x80|($val&0x3f));
                else $ret .= chr(0xe0|($val>>12)).chr(0x80|(($val>>6)&0x3f)).chr(0x80|($val&0x3f));
                $i += 5;
            }
            else if ($str[$i] == '%')
            {
                $ret .= urldecode(substr($str, $i, 3));
                $i += 2;
            }
            else $ret .= $str[$i];
        }
        return mb_convert_encoding($ret, 'gbk', 'utf-8');
    }

    public static function judgeEqual($key1, $key2)
    {
        $a = array_diff($key1,$key2);
        $b = array_diff($key2,$key1);
        if(empty($a) && empty($b)){
            return true;
        }else{
            return false;
        }
    }

    public static function interChange($a, $b, $array)
    {
        if (false == is_array($array))
        {
            throw new BizException('传入参数错误!');
        }

        if ($a == $b)
        {
            return $array;
        }

        $res = array();
        foreach ($array as $key => $value)
        {
            if ($value == $a)
            {
                $res[$key] = $b;
            }
            elseif ($value == $b)
            {
                $res[$key] = $a;
            }
            else
            {
                $res[$key] = $value;
            }
        }

        return $res;
    }


    //3个参数:数组,要截取的数组元素，截取长度
    public static function strString(array $sets, $start, $length)
    {
        $otherLen = "";    //去掉要截取的数组元素的剩余长度
        $totalLen = "";    //总长度
        foreach ($sets as $key=>$value) {
            if ($start !== $key)
                $otherLen += strlen(strip_tags($value));
            $totalLen += strlen(strip_tags($value));
        }
        if ($length < $totalLen) {
            $sets[$start] = substr($sets[$start], 0, $length - $otherLen);
            $sets[$start] = mb_substr($sets[$start], 0, -1, 'gbk');
        }
        return strip_tags(implode('', $sets));
    }

    public static function convertArrayToGbk($arr)
    {
        if (!is_array($arr)) {
            return self::convertToGbk($arr);
        }
        else
        {
            foreach ($arr as $key => $val )
            {
                $arr[$key] = self::convertArrayToGbk($val);
            }
            return $arr;
        }
    }

    /**
     * 加密电话号码
     *
     * @param string $phone
     * @static
     * @access public
     * @return string
     */
    public static function hiddenTelNumber($phone)
    {
        $kindOf = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i',$phone); //固定电话
        if ($kindOf == 1)
        {
            return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i','$1****$2',$phone);

        }
        return  preg_replace('/(1[34578]{1}[0-9])[0-9]{5}([0-9]{2})/i','$1*****$2',$phone);
    }

    //3个调用点
    public static function hiddenEmail($email)
    {
        $hiddenStr = '';
        if (self::isEmail($email))
        {
            list($header, $footer) = explode('@', $email);
            $hiddenStr = substr($header, 0, 3)."****@".$footer;
        }
        return $hiddenStr;
    }

    public static function hiddenPartIdentificationCard($idCard)
    {
        if(strlen($idCard) < 11)
        {
            return $idCard;
        }

        return substr_replace($idCard, "******", 6, 8);
    }

    public static function hiddenPartOtherCard($idCard)
    {
        if(strlen($idCard) < 6)
        {
            return $idCard;
        }
        $startString = substr($idCard, 0, 1);
        $endString = substr($idCard, -4, 4);

        return $startString.str_repeat("*", (strlen($idCard) - 5)).$endString;
    }

    public static function calculateBalancePrice($bigPrice, $smallPrice = 0)
    {
        return number_format($bigPrice - $smallPrice, '2', '.', '');
    }

    public static function convertUTF8ToGBK($str)
    {
        $encode = mb_detect_encoding($str, array('UTF-8', 'GBK'));
        if ($encode == 'UTF-8')
        {
            $str = mb_convert_encoding($str, 'GBK', 'UTF-8');
        }
        return $str;
    }

    public static function inString($str, array $keyWords)
    {
        if (empty($keyWords) || empty($str)) return false;

        foreach ($keyWords as $keyWord)
        {
            if (stristr($str, $keyWord))
            {
                return true;
            }
        }
        return false;
    }

    //TODO:hongchao 这个方法有什么必要性？
    public static function replaceWord2Num($str)
    {
        $replace = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
        $to      = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5');
        return str_replace($replace, $to, $str);
    }

    /**
     * turnDecimalismTo62 10进制和62进制的相互转换
     *
     * @param mixed $orderId
     * @static
     * @access public
     * @return void
     */
    public static function turnDecimalismTo62($orderId)
    {
        $base = 62;
        $index = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $ret = '';
        for($t = floor(log10($orderId) / log10($base)); $t >= 0; $t--)
        {
            $a = floor($orderId / pow($base, $t));
            $ret .= substr($index, $a, 1);
            $orderId -= $a * pow($base, $t);
        }
        return $ret;
    }

    public static function turn62ToDecimalism($str)
    {
        $base = 62;
        $index = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $ret = 0;
        $length = strlen($str) - 1;
        for($t = 0; $t <= $length; $t++)
        {
            $ret += strpos($index, substr($str, $t, 1)) * pow($base, $length - $t);
        }
        return $ret;
    }
}
