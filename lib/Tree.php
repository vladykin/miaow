<?php

// $Id$

require_once(LIB_DIR . '/Storage.php');
require_once(LIB_DIR . '/TreeNode.php');
require_once(LIB_DIR . '/TreePath.php');

class Tree {

    /**
     * This is utility class that can't be instantiated.
     */
    private function __construct() {}

    public static function findPath(TreeNode $node) {
        $db = Storage::getConnection();
        $nodes = array();
        $nodes[] = $node;
        while ($node->getParentId() > 0) {
            $query = 'SELECT * FROM ! WHERE id = ?';
            $res = $db->getRow($query, array(TreeNode::getTableName(), $node->getParentId()));
            assert('!DB::isError($res) && is_array($res)');
            $node = EntityManager::decode($res, 'TreeNode');
            $nodes[] = $node;
        }
        $path = new TreePath();
        for ($i = count($nodes) - 1; $i >= 0; --$i) {
            $path->pushNode($nodes[$i]);
        }
        return $path;
    }

    /**
     * @return TreePath
     */
    public static function resolvePath($path) {
        assert('is_string($path)');
        $db = Storage::getConnection();
        $query = 'SELECT * FROM `!` WHERE `parentId` = ?';
        $res = $db->getRow($query, array(TreeNode::getTableName(), 0));
        assert('is_array($res) && !DB::isError($res)');
        $node = EntityManager::decode($res, 'TreeNode');
        $nodes = array($node);
        $path = explode('/', $path);
        if (!empty($path) && strlen($path[0]) == 0) { array_shift($path); }
        if (!empty($path) && strlen(end($path)) == 0) { array_pop($path); }
        foreach ($path as $name) {
            if ($name == '.') {
                continue;
            }
            $query = 'SELECT * FROM `!` WHERE `parentId` = ? AND `name` = ?';
            $res = $db->getRow($query, 
                array(TreeNode::getTableName(), $node->getId(), $name));
            if (is_null($res) || DB::isError($res)) {
                return null;
            }
            $node = EntityManager::decode($res, 'TreeNode');
            $nodes[] = $node;
        }
        return new TreePath($nodes);
    }

    function persistNode(TreeNode $node, TreePath $path = null) {
        if (isset($path)) {
            if ($path->getNodeCount() > 0) {
                for ($i = 0; $i < $path->getNodeCount(); ++$i) {
                    $pathElem = $path->getNode($i);
                    if ($pathElem->getId() == $node->getId()) {
                        // attempt make node a descendant of itself
                        return false;
                    }
                }
                $parentNode = $path->getNode();
                $parentId = $parentNode->getId();
            } else {
                $parentId = 0;
            }
        } else {
            $parentId = $node->getParentId();
        }
        $db = Storage::getConnection();
        $query = 'SELECT COUNT(*) FROM `!` WHERE `parentId` = ? AND `name` = ? AND `id` \!= ?';
        $count = $db->getOne($query, array(
                TreeNode::getTableName(), $parentId, $node->getName(), $node->getId()));
        assert('!DB::isError($count)');
        if ($count != 0) {
            // log('Duplicate name: ' . $node->getName() . "\n");
            return false;
        }
        $node->setParentId($parentId);
        EntityManager::persist($node);
        return true;
    }

    function removeNode(TreeNode $node, $recursive = false) {
        assert('is_bool($recursive)');
        assert('$node->getId() > 0');
        $db = Storage::getConnection();
        $query = 'SELECT COUNT(*) FROM `!` WHERE `parentId` = ?';
        $childCount = $db->getOne($query, array(
                TreeNode::getTableName(), $node->getId()));
        assert('!DB::isError($childCount)');
        if ($recursive && $childCount > 0) {
            $query = 'SELECT * FROM `!` WHERE `parentId` = ?';
            $res = $db->getAll($query, array(
                    TreeNode::getTableName(), $node->getId()));
            foreach ($res as $row) { 
                $childNode = EntityManager::decode($row, 'TreeNode');
                Tree::removeNode($childNode, true);
            }
        }
        if ($recursive || $childCount == 0) {
            EntityManager::remove($node);
            return true;
        } else {
            return false;
        }
    }

}

?>
