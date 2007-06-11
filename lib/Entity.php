<?php

// $Id$

require_once('lib/Storage.php');

class Field {
    var $name;
    var $decl;
    var $indexed;
    function Field($name, $decl, $indexed = false) {
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($decl) && strlen($decl) > 0');
        assert('is_bool($indexed)');
        $this->name = $name;
        $this->decl = $decl;
        $this->indexed = $indexed;
    }
    function getName() {
        return $this->name;
    }
    function getDecl() {
        return $this->decl;
    }
    function isIndexed() {
        return $this->indexed;
    }
    function getGetter() {
        return 'get' . ucfirst($this->name);
    }
    function getSetter() {
        return 'set' . ucfirst($this->name);
    }
    function encode($value) {
        return isset($value)? (string)$value : null;
    }
    function decode($value) {
        return $value;
    }
}

class IntField extends Field {
    function IntField($name, $decl, $indexed = false) {
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($decl) && strlen($decl) > 0');
        assert('is_bool($indexed)');
        parent::Field($name, $decl, $indexed);        
    }
    function decode($value) {
        assert('preg_match(\'/^[\+\-]?\d+$/\', $value)');
        return (int)$value;
    }
}

class BoolField extends Field {
    function BoolField($name, $decl, $indexed = false) {
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($decl) && strlen($decl) > 0');
        assert('is_bool($indexed)');
        parent::Field($name, $decl, $indexed);
    }
    function encode($value) {
        assert('is_bool($value)');
        return (int)$value;
    }
    function decode($value) {
        assert('$value === \'0\' || $value === \'1\'');
        return $value === '1';
    }
}

class TextField extends Field {
    function TextField($name, $decl, $indexed = false) {
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($decl) && strlen($decl) > 0');
        assert('is_bool($indexed)');
        parent::Field($name, $decl, $indexed);
    }
    function encode($value) {
        return isset($value)?
            iconv('UTF-8', DB_CHARSET, $value) : null;
    }
    function decode($value) {
        return isset($value)?
            iconv(DB_CHARSET, 'UTF-8', $value) : null;
    }
}

class SerializedField extends Field {
    function SerializedField($name, $decl) {
        // serialized field can't be indexed
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($decl) && strlen($decl) > 0');
        parent::Field($name, $decl, false);
    }
    function encode($value) {
        return serialize($value);
    }
    function decode($value) {
        return unserialize($value);
    }
}

class ForeignKeyField extends Field {
    function ForeignKeyField($name, $foreignClass) {
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($foreignClass) && class_exists($foreignClass)');
        eval('$foreignPK = ' . $foreignClass . '::getPrimaryKeyField();');
        parent::Field($name, $foreignPK->getDecl());
    }
}


class Entity {
    function getTableName() {
        die('abstract function Entity::getTableName()');
    }
    function getFields() {
        die('abstract function Entity::getFields()');
    }
    function getPrimaryKeyField() {
        die('abstract function Entity::getPrimaryKeyField()');
    }
    function getField($className, $fieldName) {
        assert('is_string($className) && class_exists($className)');
        assert('is_string($fieldName) && strlen($fieldName) > 0');
        eval('$fields = ' . $className . '::getFields();');
        foreach ($fields as $field) {
            if ($field->getName() == $fieldName) {
                return $field;
            }
        }
        return null;
    }
}


class EntityManager {

    function decode($row, $entityClass) {
        assert('is_array($row)');
        assert('is_string($entityClass) && class_exists($entityClass)');
        $entity = new $entityClass();
        eval('$fields = ' . $entityClass . '::getFields();');
        for ($i = 0; $i < count($fields); ++$i) {
            $field = $fields[$i];
            $fieldName = $field->getName();
            $setterName = $field->getSetter();
            $entity->$setterName($field->decode($row[$i]));
        }
        return $entity;
    }

    function find($entityClass, $id) {
        assert('is_string($entityClass) && class_exists($entityClass)');
        assert('is_int($id) || is_string($id)');
        $db = Storage::getConnection();
        eval('$tableName = ' . $entityClass . '::getTableName();');
        $query = 'SELECT * FROM `' . $tableName . '` WHERE id = ?';
        $res = $db->getRow($query, array($id));
        assert('!DB::isError($res)');
        if (is_array($res)) {
            return EntityManager::decode($res, $entityClass);
        } else {
            return null;
        }
    }

    function persist(/*Entity */&$entity) {
        assert('is_object($entity)');
        $entityClass = get_class($entity);
        $db = Storage::getConnection();
        eval('$tableName = ' . $entityClass . '::getTableName();');
        eval('$fields = ' . $entityClass . '::getFields();');
        if (($id = $entity->getId()) == 0) {
            $id = $db->nextId($tableName, false);
            assert('!DB::isError($id)');
            $entity->setId($id);
            $query = 'INSERT INTO ! VALUES (';
            $args = array($tableName);
            $first = true;
            foreach ($fields as $field) {
                $fieldName = $field->getName();
                if ($first) {
                    $first = false;
                } else {
                    $query .= ', ';
                }
                $query .= '?';
                $getterName = $field->getGetter();
                $args[] = $field->encode($entity->$getterName());
            }
            $query .= ')';
            $res = $db->query($query, $args);
            assert('!DB::isError($res)');
        } else {
            $query = 'UPDATE ! SET ';
            $args = array($tableName);
            $first = true;
            foreach ($fields as $field) {
                $fieldName = $field->getName();
                if ($fieldName == 'id') {
                    continue;
                }
                if ($first) {
                    $first = false;
                } else {
                    $query .= ', ';
                }
                $query .= '`' . $fieldName . '` = ?';
                $getterName = $field->getGetter();
                $args[] = $field->encode($entity->$getterName());
            }
            $query .= ' WHERE `id` = ?';
            $args[] = $id;
            $res = $db->query($query, $args);
            assert('!DB::isError($res)');
        }
    }

    function remove(/*Entity */&$entity) {
        assert('is_object($entity)');
        assert('$entity->getId() > 0');
        $db = Storage::getConnection();
        eval('$tableName = ' . get_class($entity) . '::getTableName();');
        $query = 'DELETE FROM ! WHERE `id` = ?';
        $res = $db->query($query, array($tableName, $entity->getId()));
        assert('!DB::isError($res)');
        $entity->setId(0);
    }

    function installEntityClass($entityClass) {
        assert('is_string($entityClass) && class_exists($entityClass)');
        $db = Storage::getConnection();
        eval('$tableName = ' . $entityClass . '::getTableName();');
        eval('$fields = ' . $entityClass . '::getFields();');
        eval('$pk = ' . $entityClass . '::getPrimaryKeyField();');
        $query = 'CREATE TABLE `' . $tableName . '` (';
        foreach ($fields as $field) {
            $query .= '`' . $field->getName() . '` ' . $field->getDecl() . ', ';
        }
        $query .= 'PRIMARY KEY(`' . $pk->getName() . '`)';
        foreach ($fields as $field) {
            if ($field->isIndexed()) {
                $query .= ', INDEX(`' . $field->getName() . '`)';
            }
        }
        $query .= ')';
        $res = $db->query($query);
        assert('!DB::isError($res)');
        $res = $db->createSequence($tableName);
        assert('!DB::isError($res)');
    }

    function uninstallEntityClass($entityClass) {
        assert('is_string($entityClass)');
        $db = Storage::getConnection();
        eval('$tableName = ' . $entityClass . '::getTableName();');
        $res = $db->query('DROP TABLE `' . $tableName . '`');
        assert('!DB::isError($res) || $res->getCode() == DB_ERROR_NOSUCHTABLE');
        $db->dropSequence($tableName);
    }

}

?>
