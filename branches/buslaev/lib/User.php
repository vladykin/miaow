<?php

// $Id$

require_once(LIB_DIR . '/Entity.php');
require_once(LIB_DIR . '/Relation.php');
require_once(LIB_DIR . '/Util.php');

class User extends Entity {

    public static function getTableName() {
        return TABLE_PREFIX . __CLASS__;
    }

    public static function getFields() {
        return array(
            new IntField('id', 'INT UNSIGNED NOT NULL'),
            new TextField('name', 'VARCHAR(128) NOT NULL'),
            new TextField('email', 'VARCHAR(128)'),
            new TextField('password', 'VARCHAR(64)')
        );
    }

    public static function getPrimaryKeyField() {
        return Entity::getField(__CLASS__, 'id');
    }

    private $id;
    private $name;
    private $email;
    private $password;

    public function __construct() {
        $this->setId(0);
    }

    /**
     * @return integer
     */
    public function getId() {
        assert('isset($this->id)');
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        assert('Util::isValidId($id)');
        $this->id = intval($id);
    }

    /**
     * @return string
     */
    public function getName() {
        assert('isset($this->name)');
        return $this->name;
    }

    public function setName($name) {
        assert('is_string($name) && strlen($name) > 0');
        $this->name = $name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        assert('is_null($email) || Util::isValidEmail($email)');
        $this->email = $email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        assert('is_null($password) || is_string($password) && strlen($password) > 0');
        $this->password = $password;
    }

    public function isAdmin() {
        return $this->password != 'guest';
    }

}

class CreatedBy /*implements Relation */{
    public static function getSubjectClass() {
        return 'TreeNode';
    }
    public static function getObjectClass() {
        return 'User';
    }
}

?>
