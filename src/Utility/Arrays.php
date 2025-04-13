<?php

namespace PhpApi\Utility;

class Arrays
{
    /**
     *
     * @param T[] $array
     * @return ?T
     * @template T
     * @description Returns the first element of an array or null if the array is empty.
     */
    public static function getFirstElement(array $array): mixed
    {
        foreach ($array as $element) {
            return $element;
        }

        return null;
    }

    /**
     * @phan-file-suppress PhanTypeArraySuspicious
     * @phan-file-suppress PhanPartialTypeMismatchReturn
     */
    public static function groupBy(array $array, int|string|float|callable $key): array
    {
        $func = (!is_string($key) && is_callable($key) ? $key : null);
        $_key = $key;

        // Load the new array, splitting by the target key
        $grouped = [];
        foreach ($array as $value) {
            $key = null;

            if (is_callable($func)) {
                $key = call_user_func($func, $value);
            } elseif (is_object($value) && is_string($_key) && property_exists($value, $_key)) {
                $key = $value->{$_key};
            } elseif (isset($value[$_key])) {
                $key = $value[$_key];
            }

            if ($key === null) {
                continue;
            }

            $grouped[$key][] = $value;
        }

        // Recursively build a nested grouping if more parameters are supplied
        // Each grouped array value is grouped according to the next sequential key
        if (func_num_args() > 2) {
            $args = func_get_args();

            foreach ($grouped as $key => $value) {
                $params = array_merge([ $value ], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array([ __CLASS__, 'groupBy' ], $params);
            }
        }

        return $grouped;
    }
}
