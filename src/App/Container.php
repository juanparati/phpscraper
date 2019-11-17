<?php

/**
 * Class Container.
 *
 * It implements a basic container service.
 *
 * Static container helper.
 *
 * @method static set(string $name, $instance)
 * @method static get
 */
class Container
{
    /**
     * Static container.
     *
     * @var array
     */
    protected static $container = [];


    /**
     * Provide getter and setter for the static container.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if ('set' === $name) {
            [$key, $data] = $arguments;

            return static::$container[$key] = $data;
        } elseif ('get' === $name) {
            return static::$container[$arguments[0]];
        }

        return static::$container[$name];
    }
}
