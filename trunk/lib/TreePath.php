<?php

// $Id$

require_once(dirname(__FILE__) . '/Util.php');

class TreePath {

    /**
     * @access private
     */
    var $nodes;

    /**
     * @access public
     * @param array $nodes
     */
    function TreePath($nodes = array()) {
        assert('Util::isArrayOf($nodes, \'TreeNode\')');
        $this->nodes = $nodes;
    }

    /**
     * @access public
     * @return TreeNode
     */
    function getNode($index = null) {
        assert('isset($this->nodes)');
        assert('is_null($index) || is_int($index) && 0 <= $index && $index < count($this->nodes)');
        return isset($index)? 
            $this->nodes[$index] : 
            $this->nodes[count($this->nodes) - 1];
    }

    function getNodeCount() {
        assert('isset($this->nodes)');
        return count($this->nodes);
    }

    /**
     * @access public
     * @param TreeNode $node
     */
    function pushNode(&$node) {
        assert('is_a($node, \'TreeNode\')');
        if (!empty($this->nodes)) {
            $prevnode = end($this->nodes);
            assert('$prevnode->getId() == $node->getParentId()');
        }
        $this->nodes[] = $node;
    }
    
    /**
     * @access public
     */
    function popNode() {
        assert('!empty($this->nodes)');
        return array_pop($this->nodes);
    }


    /**
     * @access public
     * @return string
     */
    function getDirectory() {
        assert('is_array($this->nodes) && !empty($this->nodes)');
        $dirs = array();
        for ($i = 0; $i < $this->getNodeCount(); ++$i) {
            $node = $this->getNode($i);
            if ($node->getHasOwnDir()) {
                $dirs[] = $node->getName();
            }
        }
        return count($dirs) > 0? implode('/', $dirs) : '.';
    }

    /**
     * @access public
     * @return string
     */
    function toString() {
        assert('!empty($this->nodes)');
        $path = array();
        for ($i = 1; $i < $this->getNodeCount(); ++$i) {
            $node = $this->getNode($i);
            $path[] = $node->getName();
        }
        $path = implode('/', $path);
        return (strlen($path) > 0)? $path : '.';
    }

    /**
     * @access public
     * @return string
     */
    function toURL() {
        return rtrim(TREE_ROOT . '/' . $this->toString(), '/');
    }

}

?>
