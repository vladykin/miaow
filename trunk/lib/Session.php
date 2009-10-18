<?php

// $Id$

require_once(LIB_DIR . '/HTTP.php');
require_once(LIB_DIR . '/Storage.php');
require_once(LIB_DIR . '/User.php');

session_start();

class Session {

    private static $user = null;

    public static function getUser() {
        if (is_null(self::$user)) {
            $db = Storage::getConnection();
            if (array_key_exists('userId', $_SESSION)) {
                $userId = $_SESSION['userId'];
                assert('is_integer($userId) && $userId > 0');
            } else {
                $userId = 1;
            }
            $query = 'SELECT * FROM ! WHERE id = ?';
            $res = $db->getRow($query, array(User::getTableName(), $userId));
            assert('is_array($res) && !DB::isError($res)');
            self::$user = EntityManager::decode($res, 'User');
        }
        return self::$user;
    }

    public static function isPrivileged() {
        $user = self::getUser();
        return $user->getName() == 'Admin';
    }

    public static function ensurePrivileged() {
        if (!self::isPrivileged()) {
            HTTP::seeOther(SITE_URL . '/login.php?redirect='
                    . urlencode($_SERVER['REQUEST_URI']));
            exit(1);
        }
    }

    public static function doLogin($password) {
        assert('is_string($password)');
        $db = Storage::getConnection();
        $query = 'SELECT * FROM `!` WHERE `password` = ?';
        $res = $db->getRow($query, array(
                User::getTableName(), 
                Storage::encodeString($password)));
        assert('!DB::isError($res)');
        if (isset($res)) {
            self::$user = EntityManager::decode($res, 'User');
            $_SESSION['userId'] = self::$user->getId();
            return true;
        } else {
            return false;
        }
    }

    public static function doLogout() {
        self::$user = null;
        unset($_SESSION['userId']);
    }

}

?>
