<?php

// $Id$

require_once('lib/Entity.php');
require_once('lib/Relation.php');
require_once('lib/Util.php');

class Keyword extends Entity {

    public static function getTableName() {
        return TABLE_PREFIX . __CLASS__;
    }

    public static function getFields() {
        return array(
            new IntField('id', 'INT UNSIGNED NOT NULL'),
            new TextField('uri', 'VARCHAR(128) NOT NULL'),
            new TextField('title', 'VARCHAR(128) NOT NULL')
        );
    }

    public static function getPrimaryKeyField() {
        return Entity::getField(__CLASS__, 'id');
    }

    var $id;
    var $uri;
    var $title;

    public function Keyword() {
        $this->setId(0);
    }

    function getId() {
        assert('isset($this->id)');
        return $this->id;
    }

    function setId($id) {
        assert('Util::isValidId($id)');
        $this->id = $id;
    }

    function getURI() {
        assert('isset($this->uri)');
        return $this->uri;
    }

    function setURI($uri) {
        assert('is_string($uri) && strlen($uri) > 0');
        $this->uri = $uri;
    }

    function getTitle() {
        assert('isset($this->title)');
        return $this->title;
    }

    function setTitle($title) {
        assert('is_string($title) && strlen($title) > 0');
        $this->title = $title;
    }

}

class Provides /*implements Relation */{
    function getSubjectClass() {
        return 'TreeNode';
    }
    function getObjectClass() {
        return 'Keyword';
    }
}

class Requires /*implements Relation */{
    function getSubjectClass() {
        return 'TreeNode';
    }
    function getObjectClass() {
        return 'Keyword';
    }
}

?>
