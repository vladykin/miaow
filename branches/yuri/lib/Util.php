<?php

// $Id$

class Util {

    public static function isValidId($id) {
        return is_integer($id) && $id >= 0
            || is_string($id) && preg_match('/^\d+$/', $id);
    }

    public static function isValidName($name) {
        return is_string($name) 
            && $name{0} != '.'
            && preg_match('/^[a-z0-9_\.\-]+$/i', $name);
    }

    public static function isValidBoolean($bool) {
        return $bool === false || $bool === true
            || $bool === 0 || $bool === 1
            || $bool === '0' || $bool === '1';
    }

    public static function isValidInteger($int) {
        return is_integer($int) 
            || is_string($int) && preg_match('/^[\+\-]?\d+$/', $int);
                
    }

    public static function isValidDate($date) {
        return is_string($date)
            && preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $date);
    }

    public static function isValidDateTime($datetime) {
        return is_string($datetime)
            && preg_match('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $datetime);
    }

    public static function isValidEmail($email) {
        return is_string($email)
            && preg_match('/^[a-z0-9_-]+(\.[a-z0-9_-]+)*\@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,4}$/i', $email);
    }
    
    public static function isArrayOf($array, $class) {
        if (is_array($array)) {
            foreach ($array as $item) {
                if (!($item instanceof $class)) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public static function packArray($array) {
        assert('is_array($array)');
        $newarray = array();
        foreach ($array as $element) {
            $newarray[] = $element;
        }
        return $newarray;
    }

}

?>
