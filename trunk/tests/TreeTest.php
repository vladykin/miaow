<?php

// $Id$

require_once('PHPUnit/TestCase.php');

require_once('lib/Tree.php');

/**
 * Test class for TreeNode.
 */
class tests_TreeTest extends PHPUnit_TestCase {

    function setUp() {
        EntityManager::installEntityClass('TreeNode');
    }

    function tearDown() {
        EntityManager::uninstallEntityClass('TreeNode');
    }

    function cleanup() {
        $db =& Storage::getConnection();
        $query = 'DELETE FROM `!`';
        $db->query($query, TreeNode::getTableName());
    }

    function testPersistResolveRemove() {
        // create root node and its child
        $path =& new TreePath();
        $node =& new TreeNode();
        $node->setName('root');
        $node->setTitle('Root Node');
        $node->setTypeName('RootType');
        $node->setHasOwnDir(true);
        $node->setIsVisible(true);
        $result = Tree::persistNode($node, $path);
        $this->assertTrue($result);
        $path->pushNode($node);
        $node =& new TreeNode();
        $node->setName('child');
        $node->setTitle('Child Node');
        $node->setTypeName('ChildType');
        $node->setHasOwnDir(true);
        $node->setIsVisible(true);
        $result = Tree::persistNode($node, $path);
        $this->assertTrue($result);
        $path->pushNode($node);
        // now try to resolve TreePath object by symbolic path
        $path =& Tree::resolvePath('/child');
        $this->assertNotNull($path);
        $this->assertEquals(2, $path->getNodeCount());
        $node =& $path->getNode();
        $this->assertEquals('child', $node->getName());
        $node =& $path->getNode(0);
        $this->assertEquals('root', $node->getName());
        // try to remove root non-recursively, expect failure
        $result = Tree::removeNode($node);
        $this->assertFalse($result);
        // remove root recursively
        $result = Tree::removeNode($node, true);
        $this->assertTrue($result);
        // use native query to confirm removal
        $db =& Storage::getConnection();
        $query = 'SELECT COUNT(*) FROM `!`';
        $count =& $db->getOne($query, array(TreeNode::getTableName()));
        $this->assertFalse(DB::isError($count));
        $this->assertEquals(0, $count);
        // cleanup
        $this->cleanup();
    }

    function testPersistDuplicate() {
        // create root node and its child
        $path =& new TreePath();
        $node =& new TreeNode();
        $node->setName('root');
        $node->setTitle('Root Node');
        $node->setTypeName('RootType');
        $node->setHasOwnDir(true);
        $node->setIsVisible(true);
        $result = Tree::persistNode($node, $path);
        $this->assertTrue($result);
        $path->pushNode($node);
        $node =& new TreeNode();
        $node->setName('child');
        $node->setTitle('Child Node');
        $node->setTypeName('ChildType');
        $node->setHasOwnDir(true);
        $node->setIsVisible(true);
        $result = Tree::persistNode($node, $path);
        $this->assertTrue($result);
        // create another similarly named child
        $node =& new TreeNode();
        $node->setName('child');
        $node->setTitle('Child Node 2');
        $node->setTypeName('ChildType');
        $node->setHasOwnDir(true);
        $node->setIsVisible(true);
        // persisting must fail
        $result = Tree::persistNode($node, $path);
        $this->assertFalse($result);
        // cleanup
        $this->cleanup();
    }

    function testMove() {
        // create and persist a root node
        $path =& new TreePath();
        $root =& new TreeNode();
        $root->setName('root');
        $root->setTitle('Root Node');
        $root->setTypeName('RootType');
        $root->setHasOwnDir(true);
        $root->setIsVisible(true);
        $result = Tree::persistNode($root, $path);
        $this->assertTrue($result);
        $path->pushNode($root);
        // create two children
        $child1 =& new TreeNode();
        $child1->setName('child1');
        $child1->setTitle('Child Node 1');
        $child1->setTypeName('ChildType');
        $child1->setHasOwnDir(true);
        $child1->setIsVisible(true);
        $result = Tree::persistNode($child1, $path);
        $this->assertTrue($result);
        $child2 =& new TreeNode();
        $child2->setName('child2');
        $child2->setTitle('Child Node 2');
        $child2->setTypeName('ChildType');
        $child2->setHasOwnDir(true);
        $child2->setIsVisible(true);
        $result = Tree::persistNode($child2, $path);
        $this->assertTrue($result);
        // move child1 under child2
        $path->pushNode($child2);
        $result = Tree::persistNode($child1, $path);
        $this->assertTrue($result);
        // let's validate
        $path =& Tree::resolvePath('/child1');
        $this->assertNull($path);
        $path =& Tree::resolvePath('/child2/child1');
        $this->assertNotNull($path);
        // cleanup
        $this->cleanup();
    }

    function testMoveToSelfChild() {
        // create and persist a root node
        $path =& new TreePath();
        $node =& new TreeNode();
        $node->setName('root');
        $node->setTitle('Root Node');
        $node->setTypeName('RootType');
        $node->setHasOwnDir(true);
        $node->setIsVisible(true);
        $result = Tree::persistNode($node, $path);
        $this->assertTrue($result);
        // now try to make root a child of itself
        $path->pushNode($node);
        $result = Tree::persistNode($node, $path);
        $this->assertFalse($result);
        // verify that root is still here
        $path =& Tree::resolvePath('/');
        $node =& $path->getNode();
        $this->assertEquals(0, $node->getParentId());
        $this->assertEquals('root', $node->getName());
        // cleanup
        $this->cleanup();
    }

    function testPersistModified() {
        // create root node
        $path =& new TreePath();
        $node =& new TreeNode();
        $node->setName('root');
        $node->setTitle('Root Node');
        $node->setTypeName('RootType');
        $node->setHasOwnDir(true);
        $node->setIsVisible(true);
        $result = Tree::persistNode($node, $path);
        $this->assertTrue($result);
        // modify some fields
        $node->setTitle('Another Title');
        $node->setIsVisible(false);
        // persisting must pass
        $result = Tree::persistNode($node);
        $this->assertTrue($result);
        // cleanup
        $this->cleanup();
    }

}

?>
