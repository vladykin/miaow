<?php

// $Id$


/**
 * Provides database connection and some helpful methods.
 */
class Storage {

    /**
     * This is utility class that can't be instantiated.
     */
    private function __construct() {}

    /**
     * @access public static
     */
    function &getConnection() {

try {
    $dbh = new PDO("mysql:host=localhost;dbname=miaow", 'root', 'kr4x8kr4x8');
    /*** echo a message saying we have connected ***/
    echo 'Connected to database';

    }
catch(PDOException $e)
    {
    echo $e->getMessage();
    }
    return $dbh;
    }

    /**
     * @access public static
     * @param $string string
     * @return string
     */
    function encodeString($string) {
        assert('is_string($string)');
        return iconv('UTF-8', DB_CHARSET, $string);
    }

    /**
     * @access public static
     * @param $string string
     * @return string
     */
    function decodeString($string) {
        assert('is_string($string)');
        return iconv(DB_CHARSET, 'UTF-8', $string);
    }

}

?>
