<?php

// $Id$

require_once('lib/Util.php');

class TreePath {

    private $nodes;

    /**
     * @param array $nodes
     */
    public function __construct($nodes = array()) {
        assert('Util::isArrayOf($nodes, \'TreeNode\')');
        $this->nodes = $nodes;
    }

    /**
     * @return TreeNode
     */
    public function getNode($index = null) {
        assert('isset($this->nodes)');
        assert('is_null($index) || is_int($index) && 0 <= $index && $index < count($this->nodes)');
        return isset($index)? 
            $this->nodes[$index] : 
            $this->nodes[count($this->nodes) - 1];
    }

    public function getNodeCount() {
        assert('isset($this->nodes)');
        return count($this->nodes);
    }

    /**
     * @param TreeNode $node
     */
    public function pushNode(TreeNode $node) {
        if (!empty($this->nodes)) {
            $prevnode = end($this->nodes);
            assert('$prevnode->getId() == $node->getParentId() || $node->getId() == 0');
        }
        $this->nodes[] = $node;
    }
    
    public function popNode() {
        assert('!empty($this->nodes)');
        return array_pop($this->nodes);
    }


    /**
     * @return string
     */
    public function getDirectory() {
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
     * @return string
     */
    public function toString() {
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
     * @return string
     */
    public function toURL() {
        return rtrim(TREE_ROOT . '/' . $this->toString(), '/');
    }

}

?>
