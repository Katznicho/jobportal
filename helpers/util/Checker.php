<?php

namespace Ssentezo\Util;

class Checker
{
    /**
     * This method checks the presence of of a value for the given array key in the specified array
     * @param array $assocArray
     * @param array $keysArray
     * @return bool true or false
     */
    public static function presenceCheck($assocArray, $keysArray)
    {
        $total = 0;
        $passed = 0;
        foreach ($keysArray as $key) {
            $total += 1;
            if (strlen($assocArray[$key]) > 0) {
                $passed += 1;
            }
        }
        if ($total == $passed) {
            return true;
        } else {
            return false;
        }
    }
}
