<?php

namespace App\Helper;

use Doctrine\ORM\QueryBuilder;

class QueryHelper
{

    public static function FilterCheck ($array, $key, $val = null)
    {
        // Array optional
        if(is_array($key)){
            foreach($key as $k){
                if((array_key_exists($k,$array) and $array[$k] != ''))
                    return $array[$k];
            }
            return false;
        }

        if(array_key_exists($key,$array)){
            if(is_array($array[$key]))
                return count($array[$key]);
            elseif(is_bool($array[$key]))
                return true;
            else
                return $val ? ($array[$key] === $val) :  ($array[$key] != '');

        }
        return false;
    }

    public static function TrueCheck ($array, $key): float
    {
        return (array_key_exists($key,$array) and $array[$key] == true);
    }

    public static function FilterCheckAll ($array, $checkArray): bool
    {
        foreach ($checkArray as $item) {
            if(!self::FilterCheck($array,$item)) return false;
        }
        return true;
    }
}