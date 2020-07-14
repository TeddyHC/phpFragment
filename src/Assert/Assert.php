<?php

namespace Assert;

/**
 * Assert.
 */
class Assert
{
    public static $needAssert = 0;

    /**
     * @param $express
     *
     * @throws AssertException
     */
    public static function requireTrue($express, string $message = '', int $code = -1)
    {
        if (!$express && self::$needAssert <= 0) {
            throw new AssertException($message, $code);
        }
    }

    /**
     * @param $express
     *
     * @throws AssertException
     */
    public static function requireFalse($express, string $message = '', int $code = -1)
    {
        if ($express && self::$needAssert <= 0) {
            throw new AssertException($message, $code);
        }
    }

    /**
     * @param $obj
     *
     * @throws AssertException
     */
    public static function requireNotNull($obj, string $message = '', int $code = -1)
    {
        self::requireTrue(null !== $obj, $message, $code);
    }

    /**
     * @param $obj
     *
     * @throws AssertException
     */
    public static function requireNull($obj, string $message = '', int $code = -1)
    {
        self::requireTrue(null === $obj, $message, $code);
    }

    /**
     * @param $str
     *
     * @throws AssertException
     */
    public static function requireNotEmptyString($str, string $message = '', int $code = -1)
    {
        self::requireTrue(null !== $str, $message, $code);
        self::requireTrue('' != trim($str), $message, $code);
    }

    /**
     * @param $array
     *
     * @throws AssertException
     */
    public static function requireNotEmptyArray($array, string $message = '', int $code = -1)
    {
        self::requireTrue(is_array($array), $message, $code);
        self::requireNotEmpty($array, $message, $code);
    }

    /**
     * @param $obj
     * @param string $message
     * @param int    $code
     *
     * @throws AssertException
     */
    public static function requireNotEmpty($obj, $message = '', $code = -1)
    {
        self::requireTrue(!empty($obj), $message, $code);
    }

    /**
     * @param $obj
     * @param string $message
     * @param int    $code
     *
     * @throws AssertException
     */
    public static function requireEmpty($obj, $message = '', $code = -1)
    {
        self::requireTrue(empty($obj), $message, $code);
    }

    /**
     * @param $first
     * @param $second
     *
     * @throws AssertException
     */
    public static function requireEquals($first, $second, string $message = '', int $code = -1)
    {
        self::requireTrue($first == $second, $message, $code);
    }

    /**
     * @param $first
     * @param $second
     *
     * @throws AssertException
     */
    public static function requireNotEquals($first, $second, string $message = '', int $code = -1)
    {
        self::requireTrue($first != $second, $message, $code);
    }

    /**
     * @param $first
     * @param $second
     *
     * @throws AssertException
     */
    public static function requireLess($first, $second, string $message = '', int $code = -1)
    {
        self::requireTrue($first < $second, $message, $code);
    }

    /**
     * @param $first
     * @param $second
     *
     * @throws AssertException
     */
    public static function requireMore($first, $second, string $message = '', int $code = -1)
    {
        self::requireTrue($first > $second, $message, $code);
    }

    /**
     * @param $first
     * @param $second
     *
     * @throws AssertException
     */
    public static function requireMoreThan($first, $second, string $message = '', int $code = -1)
    {
        self::requireTrue($first >= $second, $message, $code);
    }

    /**
     * @param $first
     * @param $second
     *
     * @throws AssertException
     */
    public static function requireLessThan($first, $second, string $message = '', int $code = -1)
    {
        self::requireTrue($first <= $second, $message, $code);
    }

    /**
     * @param $obj
     * @param $min
     * @param $max
     *
     * @throws AssertException
     */
    public static function requireBetween($obj, $min, $max, string $message = '', int $code = -1)
    {
        if (is_string($obj)) {
            $len = strlen($obj);
            self::requireTrue($len >= $min && $len <= $max, $message, $code);
        } else {
            self::requireTrue($obj >= $min && $obj <= $max, $message, $code);
        }
    }

    /**
     * @param $obj
     *
     * @throws AssertException
     */
    public static function requireObjNotNull($obj, string $message = '', int $code = -1)
    {
        foreach ($obj as $key => $value) {
            self::requireNotNull($value, "$key cannot null", $code);
        }
    }

    /**
     * @param $obj
     * @param $array
     *
     * @throws AssertException
     */
    public static function requireIn($obj, array $array, string $message = '', int $code = -1)
    {
        if (false == in_array($obj, $array)) {
            $message = $message ?: "$obj not in array : ".print_r($array, true);
            throw new AssertException($message, $code);
        }
    }

    /**
     * @param $str
     *
     * @throws AssertException
     */
    public static function requireNum($str, string $message = '', int $code = -1)
    {
        self::requireTrue(is_numeric($str), $message, $code);
    }
}
