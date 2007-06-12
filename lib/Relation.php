<?php

// $Id$

require_once('lib/Storage.php');

/*
interface Relation {
    public static function getSubjectClass();
    public static function getObjectClass();
}
*/

class RelationManager {

    public static function addTriples($relationClass, $subjId, $objId) {
        assert('is_string($relationClass) && class_exists($relationClass)');
        assert('is_int($subjId) || is_array($subjId)');
        assert('is_int($objId) || is_array($objId)');
        assert('!is_array($subjId) || !is_array($objId)');
        $db = Storage::getConnection();
        $query = 'INSERT INTO `' . $relationClass . '` VALUES ';
        if (is_int($subjId) && is_int($objId)) {
            $query .= '(' . $subjId . ', ' . $objId . ')';
        } elseif (is_array($subjId)) {
            $query .= implode(', ', array_map(create_function('$id', 'return "($id, ' . $objId . ')";'), $subjId));
        } elseif (is_array($objId)) {
            $query .= implode(', ', array_map(create_function('$id', 'return "(' . $subjId . ', $id)";'), $objId));
        } else {
            die('it\'s impossible!');
        }
        $res = $db->query($query);
        if (DB::isError($res)) var_dump($res);
        assert('!DB::isError($res)');
    }

    public static function removeTriples($relationClass, $subjId, $objId) {
        assert('is_string($relationClass) && class_exists($relationClass)');
        assert('is_null($subjId) || is_int($subjId) || is_array($subjId)');
        assert('is_null($objId) || is_int($objId) || is_array($objId)');
        assert('isset($subjId) || isset($objId)');
        $db = Storage::getConnection();
        $query = 'DELETE FROM `' . $relationClass . '` WHERE';
        if (is_int($subjId)) {
            $query .= ' `subjId` = ' . $subjId;
        } elseif (is_array($subjId)) {
            $query .= ' `subjId` IN (' . implode(', ', $subjId) . ')';
        }
        if (isset($subjId) && isset($objId)) {
            $query .= ' AND';
        }
        if (is_int($objId)) {
            $query .= ' `objId` = ' . $objId;
        } elseif (is_array($objId)) {
            $query .= ' `objId` IN (' . implode(', ', $objId) . ')';
        }
        $res = $db->query($query);
        assert('!DB::isError($res)');
    }

    public static function findTriples($relationClass, $subjId, $objId) {
        assert('is_string($relationClass) && class_exists($relationClass)');
        assert('is_null($subjId) || is_int($subjId) || is_array($subjId)');
        assert('is_null($objId) || is_int($objId) || is_array($objId)');
        assert('isset($subjId) || isset($objId)');
        $db = Storage::getConnection();
        $query = 'SELECT * FROM `' . $relationClass . '` WHERE';
        if (is_int($subjId)) {
            $query .= ' `subjId` = ' . $subjId;
        } elseif (is_array($subjId)) {
            $query .= ' `subjId` IN (' . implode(', ', $subjId) . ')';
        }
        if (isset($subjId) && isset($objId)) {
            $query .= ' AND';
        }
        if (is_int($objId)) {
            $query .= ' `objId` = ' . $objId;
        } elseif (is_array($objId)) {
            $query .= ' `objId` IN (' . implode(', ', $objId) . ')';
        }
        $res = $db->getAll($query);
        assert('!DB::isError($res)');
        return $res;
    }

    /*public static */function findEntities($relationClass, $subjId, $objId) {
        assert('is_string($relationClass) && class_exists($relationClass)');
        assert('is_null($subjId) || is_int($subjId) || is_array($subjId)');
        assert('is_null($objId) || is_int($objId) || is_array($objId)');
        assert('is_int($subjId) || is_int($objId)');
        $db = Storage::getConnection();
        eval('$subjClass = ' . $relationClass . '::getSubjectClass();');
        eval('$objClass = ' . $relationClass . '::getObjectClass();');
        $entityClass = is_int($subjId)? $objClass : $subjClass;
        eval('$entityTable = ' . $entityClass . '::getTableName();');
        $query = 'SELECT e.* FROM `' . $relationClass . '` AS r, '
                . '`' . $entityTable . '` AS e WHERE';
        if (is_int($subjId)) {
            $query .= ' r.`subjId` = ' . $subjId;
        } elseif (is_array($subjId)) {
            $query .= ' r.`subjId` IN (' . implode(', ', $subjId) . ')';
        }
        if (isset($subjId) && isset($objId)) {
            $query .= ' AND';
        }
        if (is_int($objId)) {
            $query .= ' r.`objId` = ' . $objId;
        } elseif (is_array($objId)) {
            $query .= ' r.`objId` IN (' . implode(', ', $objId) . ')';
        }
        $query .= ' AND e.`id` = r.`' . (is_int($subjId)? 'objId' : 'subjId') . '`';
        $res = $db->getAll($query);
        assert('!DB::isError($res)');
        for ($i = 0; $i < count($res); ++$i) {
            $res[$i] = EntityManager::decode($res[$i], $entityClass);
        }   
        return $res;
    }

    public static function getMapping($relationClass, Entity $subject) {
        assert('is_string($relationClass) && class_exists($relationClass)');
        return RelationManager::findEntities($relationClass, $subject->getId(), null);
    }

    public static function setMapping($relationClass, Entity $subject, $objects) {
        assert('is_string($relationClass) && class_exists($relationClass)');
        assert('Util::isArrayOf($objects, \'Entity\')');
        RelationManager::removeTriples($relationClass, $subject->getId(), null);
        RelationManager::addTriples($relationClass, $subject->getId(), array_map(
                create_function('$obj', 'return $obj->getId();'), $objects));
    }

    public static function installRelationClass($relationClass) {
        assert('is_string($relationClass) && class_exists($relationClass)');
        $db = Storage::getConnection();
        eval('$subjClass = ' . $relationClass . '::getSubjectClass();');
        eval('$subjFields = ' . $subjClass . '::getFields();');
        eval('$objClass = ' . $relationClass . '::getObjectClass();');
        eval('$objFields = ' . $objClass . '::getFields();');
        $query = 'CREATE TABLE `' . $relationClass . '` ('
                . '`subjId` ' . $subjFields[0]->getDecl() . ', '
                . '`objId` ' . $objFields[0]->getDecl() . ', '
                . 'UNIQUE (`subjId`, `objId`), '
                . 'FOREIGN KEY (`subjId`) REFERENCES `' . $subjClass . '`.`id`, '
                . 'FOREIGN KEY (`objId`) REFERENCES `' . $objClass . '`.`id`)';
        $res = $db->query($query);
        assert('!DB::isError($res)');
    }

    public static function uninstallRelationClass($relationClass) {
        assert('is_string($relationClass)');
        $db = Storage::getConnection();
        $res = $db->query('DROP TABLE `' . $relationClass . '`');
        assert('!DB::isError($res) || $res->getCode() == DB_ERROR_NOSUCHTABLE');
    }

}

?>
