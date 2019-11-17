<?php


/**
 * Class Caster.
 */
class Caster
{


    /**
     * Cast to any different data types.
     *
     * @param $value
     * @param string $type
     * @return mixed
     */
    public static function cast($value, string $type)
    {
        $method = 'as' . ucfirst($type);

        return method_exists(__CLASS__, $method) ? static::$method($value) : $value;
    }


    /**
     * Cast a string.
     *
     * @param $value
     * @return string
     */
    public static function asString($value) : string
    {
        return (string) $value;
    }

    /**
     * Cast as int.
     *
     * @param $value
     * @return int
     */
    public static function asInt($value) : int
    {
        return (int) $value;
    }

    /**
     * Cast as float.
     *
     * @param $value
     * @return float
     */
    public static function asFloat($value) : float
    {
        return (float) $value;
    }

    /**
     * Cast as boolean.
     *
     * @param $value
     * @return bool
     */
    public static function asBoolean($value) : bool
    {
        // Cast boolean as string
        if ($value === 'true')
            return true;

        if ($value === 'false')
            return false;

        return $value;
    }

}