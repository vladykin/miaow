<?php

// $Id$

require_once('lib/Entity.php');
require_once('lib/Relation.php');
require_once('lib/Util.php');

class User extends Entity {

    function getTableName() {
        return TABLE_PREFIX . __CLASS__;
    }

    function getFields() {
        return array(
            new IntField('id', 'INT UNSIGNED NOT NULL'),
            new TextField('name', 'VARCHAR(128) NOT NULL'),
            new TextField('email', 'VARCHAR(128)'),
            new TextField('password', 'VARCHAR(64)')
        );
    }

    function getPrimaryKeyField() {
        return Entity::getField(__CLASS__, 'id');
    }

    var $id;
    var $name;
    var $email;
    var $password;

    function User() {
        $this->setId(0);
    }

    /**
     * @access public
     * @return integer
     */
    function getId() {
        assert('isset($this->id)');
        return $this->id;
    }

    /**
     * @access public
     * @param mixed $id
     */
    function setId($id) {
        assert('Util::isValidId($id)');
        $this->id = intval($id);
    }

    /**
     * @access public
     * @return string
     */
    function getName() {
        assert('isset($this->name)');
        return $this->name;
    }

    function setName($name) {
        assert('is_string($name) && strlen($name) > 0');
        $this->name = $name;
    }

    function getEmail() {
        return $this->email;
    }

    function setEmail($email) {
        assert('is_null($email) || Util::isValidEmail($email)');
        $this->email = $email;
    }

    function getPassword() {
        return $this->password;
    }

    function setPassword($password) {
        assert('is_null($password) || is_string($password) && strlen($password) > 0');
        $this->password = $password;
    }

    function isAdmin() {
        return $this->password != 'guest';
    }

}

class CreatedBy /*implements Relation */{
    /*public static */function getSubjectClass() {
        return 'TreeNode';
    }
    /*public static */function getObjectClass() {
        return 'User';
    }
}

?>
