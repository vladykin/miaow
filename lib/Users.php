<?php

// $Id$

require_once('lib/Storage.php');
require_once('lib/User.php');

class Users {

    /**
     * @return User
     */
    function &getCurrentUser() {
        $db =& Storage::getConnection();
        if (array_key_exists('userId', $_SESSION)) {
            $userId = $_SESSION['userId'];
            assert('is_integer($userId) && $userId > 0');
        } else {
            $userId = 1;
        }
        $query = 'SELECT * FROM ! WHERE id = ?';
        $res =& $db->getRow($query, array(User::getTableName(), $userId));
        assert('is_array($res) && !DB::isError($res)');
        return EntityManager::decode($res, 'User');
    }

    function insertUser(&$user) {
        assert('is_a($user, \'User\')');
        assert('$user->getId() == 0');
        $db =& Storage::getConnection();
        $id =& $db->nextId(User::getTableName(), false);
        assert('!DB::isError($id)'); 
        $user->setId($id);
        $query = 'INSERT INTO ! VALUES (?, ?, ?, ?)';
        $res =& $db->query($query, array(
            DB_TABLE_USERS,
            $user->getId(),
            Storage::encodeString($user->getName()),
            $user->getEmail(),
            $user->getPassword()
        ));
        assert('!DB::isError($res)');
    	return true;
    }


    function requireLogin(&$user) {
        if (!$user->isAdmin()) {
            $redir = $_SERVER['REQUEST_URI'];
            header('Location: ' . SITE_URL . '/login.php?redirect=' . $redir);
            exit(1);
        }
    }

    function &doLogin(&$currentUser, $password) {
        assert('is_a($currentUser, \'User\')');
        assert('is_string($password)');
        $db =& Storage::getConnection();
        $query = 'SELECT * FROM `!` WHERE `password` = ?';
        $res =& $db->getRow($query, array(
                User::getTableName(), 
                Storage::encodeString($password)));
        assert('!DB::isError($res)');
        if (isset($res)) {
            $user =& EntityManager::decode($res, 'User');
            $_SESSION['userId'] = $user->getId();
            return $user;
        } else {
            return $currentUser;
        }
    }

    function doLogout() {
        unset($_SESSION['userId']);
    }

    function getAuthors(&$treeNode) {
        assert('$treeNode->getId() != 0');
        assert('is_a($treeNode, \'TreeNode\')');
        $db =& Storage::getConnection();
        $query = 'SELECT u.* FROM ! AS p2u, ! AS u WHERE p2u.pageId = ? AND p2u.userId = u.id ORDER BY u.name';
        $res =& $db->getAll($query, array(DB_TABLE_PAGE_TO_USER, DB_TABLE_USERS, $treeNode->getId()));
        assert('!DB::isError($res)');
        $users = array();
        foreach ($res as $row) {
            $users[] =& EntityManager::decode($row, 'User');
        }
        return $users;
    }

    function setAuthors(&$treeNode, &$authors) {
        assert('$treeNode->getId() != 0');
        assert('Util::isArrayOf($authors, \'User\')');
        $db =& Storage::getConnection();
        $query = 'DELETE FROM ! WHERE pageId = ?';
        $res =& $db->query($query, array(DB_TABLE_PAGE_TO_USER, $treeNode->getId()));
        assert('!DB::isError($res)');
        $query = 'INSERT INTO ! VALUES(?, ?)';
        foreach ($authors as $author) {
            $res =& $db->query($query, array(DB_TABLE_PAGE_TO_USER, $treeNode->getId(), $author->getId()));
            assert('!DB::isError($res)');
        }
        return true;
    }

    function lookup($name) {
        assert('is_string($name) && strlen($name) > 0');
        $db =& Storage::getConnection();
        $query = 'SELECT * FROM ! WHERE name = ?';
        $res =& $db->getRow($query, array(DB_TABLE_USERS, Storage::encodeString($name)));
        assert('!DB::isError($res)');
        if ($res) {
            return EntityManager::decode($res, 'User');
        } else {
            return null;
        }
    }

}

?>
