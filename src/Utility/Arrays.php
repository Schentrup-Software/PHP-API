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
}
