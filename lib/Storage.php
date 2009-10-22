<?php

// $Id$

require_once('DB.php');

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
        static $connection = null;
        if (is_null($connection)) {
            $connection = DB::connect(DB_CONNECT_STRING);
            assert('!DB::isError($connection)');
            //$res = $connection->query('SET CHARACTER SET \'utf8\'');
            //assert('!DB::isError($res)');
        }
        return $connection;
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
