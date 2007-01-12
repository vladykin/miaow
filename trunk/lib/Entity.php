<?php

// $Id:$

require_once('lib/Storage.php');

class Field {
    private $name;
    private $decl;
    private $indexed;
    public function Field($name, $decl, $indexed = false) {
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($decl) && strlen($decl) > 0');
        assert('is_bool($indexed)');
        $this->name = $name;
        $this->decl = $decl;
        $this->indexed = $indexed;
    }
    public function getName() {
        return $this->name;
    }
    public function getDecl() {
        return $this->decl;
    }
    public function isIndexed() {
        return $this->indexed;
    }
    public function getGetter() {
        return 'get' . ucfirst($this->name);
    }
    public function getSetter() {
        return 'set' . ucfirst($this->name);
    }
    public function encode($value) {
        return (string)$value;
    }
    public function decode($value) {
        return $value;
    }
}

class IntField extends Field {
    public function IntField($name, $decl, $indexed = false) {
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($decl) && strlen($decl) > 0');
        assert('is_bool($indexed)');
        parent::Field($name, $decl, $indexed);        
    }
    public function decode($value) {
        assert('preg_match(\'/^[\+\-]?\d+$/\', $value)');
        return (int)$value;
    }
}

class BoolField extends Field {
    public function BoolField($name, $decl, $indexed = false) {
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($decl) && strlen($decl) > 0');
        assert('is_bool($indexed)');
        parent::Field($name, $decl, $indexed);
    }
    public function encode($value) {
        assert('is_bool($value)');
        return (int)$value;
    }
    public function decode($value) {
        assert('$value === \'0\' || $value === \'1\'');
        return $value === '1';
    }
}

class TextField extends Field {
    public function TextField($name, $decl, $indexed = false) {
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($decl) && strlen($decl) > 0');
        assert('is_bool($indexed)');
        parent::Field($name, $decl, $indexed);
    }
    public function encode($value) {
        return iconv('UTF-8', DB_CHARSET, $value);
    }
    public function decode($value) {
        return iconv(DB_CHARSET, 'UTF-8', $value);
    }
}

class SerializedField extends Field {
    public function SerializedField($name, $decl) {
        // serialized field can't be indexed
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($decl) && strlen($decl) > 0');
        parent::Field($name, $decl, false);
    }
    public function encode($value) {
        return serialize($value);
    }
    public function decode($value) {
        return unserialize($value);
    }
}

class ForeignKeyField extends Field {
    public function ForeignKeyField($name, $foreignClass) {
        assert('is_string($name) && strlen($name) > 0');
        assert('is_string($foreignClass) && class_exists($foreignClass)');
        eval('$foreignPK = ' . $foreignClass . '::getPrimaryKeyField();');
        parent::Field($name, $foreignPK->getDecl());
    }
}


abstract class Entity {
    public static abstract function getTableName();
    public static abstract function getFields();
    public static abstract function getPrimaryKeyField();
    public static function getField($className, $fieldName) {
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

    public static function decode($row, $entityClass) {
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

    public static function find($entityClass, $id) {
        assert('is_string($entityClass) && class_exists($entityClass)');
        assert('is_int($id) || is_string($id)');
        $db =& Storage::getConnection();
        $query = 'SELECT * FROM `' . $entityClass . '` WHERE id = ?';
        $res =& $db->getRow($query, array($id));
        assert('!DB::isError($res)');
        if (is_array($res)) {
            return EntityManager::decode($res, $entityClass);
        } else {
            return null;
        }
    }

    public static function persist(Entity &$entity) {
//        assert('is_object($entity)');
        $entityClass = get_class($entity);
        $db =& Storage::getConnection();
        eval('$fields = ' . $entityClass . '::getFields();');
        if (($id = $entity->getId()) == 0) {
            $id =& $db->nextId($entityClass, false);
            assert('!DB::isError($id)');
            $entity->setId($id);
            $query = 'INSERT INTO ! VALUES (';
            $args = array($entityClass);
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
            $res =& $db->query($query, $args);
            assert('!DB::isError($res)');
        } else {
            $query = 'UPDATE ! SET ';
            $args = array($entityClass);
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
            $res =& $db->query($query, $args);
            assert('!DB::isError($res)');
        }
    }

    public static function remove(Entity &$entity) {
//        assert('is_object($entity)');
        assert('$entity->getId() > 0');
        $db =& Storage::getConnection();
        $query = 'DELETE FROM ! WHERE `id` = ?';
        $res =& $db->query($query, array(get_class($entity), $entity->getId()));
        assert('!DB::isError($res)');
        $entity->setId(0);
    }

    public static function installEntityClass($entityClass) {
        assert('is_string($entityClass) && class_exists($entityClass)');
        $db =& Storage::getConnection();
        eval('$fields = ' . $entityClass . '::getFields();');
        eval('$pk = ' . $entityClass . '::getPrimaryKeyField();');
        $query = 'CREATE TABLE `' . $entityClass . '` (';
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
        $res =& $db->query($query);
        assert('!DB::isError($res)');
        $res =& $db->createSequence($entityClass);
        assert('!DB::isError($res)');
    }

    public static function uninstallEntityClass($entityClass) {
        assert('is_string($entityClass)');
        $db =& Storage::getConnection();
        $res =& $db->query('DROP TABLE `' . $entityClass . '`');
        $db->dropSequence($entityClass);
    }

}

?>