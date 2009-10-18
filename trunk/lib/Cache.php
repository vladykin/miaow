<?php

// $Id$

require_once(LIB_DIR . '/Storage.php');

class Cache {

    function clear() {
        $db = Storage::getConnection();
        $res = $db->query('DELETE FROM `!`', array(TABLE_PREFIX . 'Cache'));
        assert('!DB::isError($res)');
    }

    function get($name) {
        assert('is_string($name)');
        assert('strlen($name) > 0');
        $db = Storage::getConnection();
        $query = 'SELECT `value` FROM `!` WHERE `name` = ?';
        $res = $db->getOne($query, array(TABLE_PREFIX . 'Cache', $name));
        assert('!DB::isError($res)');
        return isset($res)? unserialize($res) : null;
    }

    function put($name, $value) {
        assert('is_string($name)');
        assert('strlen($name) > 0');
        $db = Storage::getConnection();
        $query = 'DELETE FROM `!` WHERE `name` = ?';
        $res = $db->query($query, array(TABLE_PREFIX . 'Cache', $name));
        assert('!DB::isError($res)');
        if (isset($value)) {
            $query = 'INSERT INTO `!` VALUES (?, ?)';
            $res = $db->query($query, 
                array(TABLE_PREFIX . 'Cache', $name, serialize($value)));
            assert('!DB::isError($res)');
        }
    }

    function install() {
        $db = Storage::getConnection();
        $res = $db->query('DROP TABLE IF EXISTS `' . TABLE_PREFIX . 'Cache`');
        assert('!DB::isError($res)');
        $res = $db->query(
            'CREATE TABLE `' . TABLE_PREFIX . 'Cache` (' .
            'name VARCHAR(128) NOT NULL PRIMARY KEY,' .
            'value TEXT NOT NULL)'
        );
        assert('!DB::isError($res)');
    }

    function uninstall() {
        $db = Storage::getConnection();
        $res = $db->query('DROP TABLE `' . TABLE_PREFIX . 'Cache`');
        assert('!DB::isError($res) || $res->getCode() == DB_ERROR_NOSUCHTABLE');
    }

}

?>
