<?php

// $Id$

require_once('PHPUnit/Framework/TestCase.php');

require_once('lib/TreeNode.php');

/**
 * Test class for TreeNode.
 */
class tests_TreeNodeTest extends PHPUnit_TestCase {

    function setUp() {
        EntityManager::installEntityClass('TreeNode');
    }

    function tearDown() {
        EntityManager::uninstallEntityClass('TreeNode');
    }

    function testPersistFindRemove() {
        // create and persist a tree node
        $entity = new TreeNode();
        $this->assertEquals(0, $entity->getId());
        $entity->setParentId(0);
        $entity->setName('root');
        $entity->setTitle('Root Node');
        $entity->setTypeName('RootType');
        $entity->setHasOwnDir(true);
        $entity->setIsVisible(true);
        $entity->setDateCreated('2006-01-01');
        $entity->setDatePublished('2007-02-02');
        $entity->setDateModified('2008-03-03');
        $entity->setPriority(-3);
        $entity->setProperty('aaa', 'bbb');
        EntityManager::persist($entity);
        $id = $entity->getId();
        $this->assertTrue($entity->getId() > 0);
        // now try to get it back
        $entity = EntityManager::find('TreeNode', $id);
        $this->assertNotNull($entity);
        $this->assertEquals($id, $entity->getId());
        $this->assertEquals(0, $entity->getParentId());
        $this->assertEquals('root', $entity->getName());
        $this->assertEquals('Root Node', $entity->getTitle());
        $this->assertEquals('RootType', $entity->getTypeName());
        $this->assertEquals(true, $entity->getHasOwnDir());
        $this->assertEquals(true, $entity->getIsVisible());
        $this->assertEquals('2006-01-01', $entity->getDateCreated());
        $this->assertEquals('2007-02-02', $entity->getDatePublished());
        $this->assertEquals('2008-03-03', $entity->getDateModified());
        $this->assertEquals(-3, $entity->getPriority());
        $this->assertEquals('bbb', $entity->getProperty('aaa'));
        // remove tree node from storage
        EntityManager::remove($entity);
        // check that it has been removed
        $entity = EntityManager::find('TreeNode', $id);
        $this->assertNull($entity);
    }

    function testUpdate() {
        // create and persist a tree node
        $entity = new TreeNode();
        $entity->setParentId(0);
        $entity->setName('root');
        $entity->setTitle('Root Node');
        $entity->setTypeName('RootType');
        $entity->setHasOwnDir(true);
        $entity->setIsVisible(true);
        $entity->setDateCreated('2006-01-01');
        $entity->setDatePublished('2007-02-02');
        $entity->setDateModified('2008-03-03');
        $entity->setPriority(-3);
        $entity->setProperty('aaa', 'bbb');
        EntityManager::persist($entity);
        $id = $entity->getId();
        // modify properties and persist it again
        $entity->setParentId(1);
        $entity->setName('news');
        $entity->setTitle('News Node');
        $entity->setTypeName('NewsType');
        $entity->setHasOwnDir(false);
        $entity->setIsVisible(false);
        $entity->setDateCreated('2005-01-01');
        $entity->setDatePublished('2006-02-02');
        $entity->setDateModified('2007-03-03');
        $entity->setPriority(5);
        $entity->setProperty('aaa', 'ccc');
        EntityManager::persist($entity);
        // get it back and verify updates
        $entity = EntityManager::find('TreeNode', $id);
        $this->assertEquals($id, $entity->getId());
        $this->assertEquals(1, $entity->getParentId());
        $this->assertEquals('news', $entity->getName());
        $this->assertEquals('News Node', $entity->getTitle());
        $this->assertEquals('NewsType', $entity->getTypeName());
        $this->assertEquals(false, $entity->getHasOwnDir());
        $this->assertEquals(false, $entity->getIsVisible());
        $this->assertEquals('2005-01-01', $entity->getDateCreated());
        $this->assertEquals('2006-02-02', $entity->getDatePublished());
        $this->assertEquals('2007-03-03', $entity->getDateModified());
        $this->assertEquals(5, $entity->getPriority());
        $this->assertEquals('ccc', $entity->getProperty('aaa'));
    }

    function testNativeQuery() {
        // create and persist a tree node
        $entity = new TreeNode();
        $entity->setParentId(1);
        $entity->setName('native-query-1');
        $entity->setTitle('Native Query 1');
        $entity->setTypeName('NQType');
        $entity->setHasOwnDir(true);
        $entity->setIsVisible(true);
        $entity->setDateCreated('2001-01-01');
        $entity->setDatePublished('2001-02-02');
        $entity->setDateModified('2001-03-03');
        $entity->setPriority(1);
        EntityManager::persist($entity);
        // create and persist another tree node
        $entity = new TreeNode();
        $entity->setParentId(2);
        $entity->setName('native-query-2');
        $entity->setTitle('Native Query 2');
        $entity->setTypeName('NQType');
        $entity->setHasOwnDir(false);
        $entity->setIsVisible(false);
        $entity->setDateCreated('2002-01-01');
        $entity->setDatePublished('2002-02-02');
        $entity->setDateModified('2002-03-03');
        $entity->setPriority(2);
        EntityManager::persist($entity);
        $id = $entity->getId();
        // now issue a native query to storage
        $db = Storage::getConnection();
        $query = 'SELECT * FROM `!` WHERE `name` = ? AND \!`isVisible`';
        $row = $db->getRow($query, array(
            TreeNode::getTableName(), 'native-query-2'));
        $this->assertType('array', $row);
        $entity = EntityManager::decode($row, 'TreeNode');
        // check that we've got expected result
        $this->assertEquals($id, $entity->getId());
        $this->assertEquals(2, $entity->getParentId());
        $this->assertEquals('native-query-2', $entity->getName());
        $this->assertEquals('Native Query 2', $entity->getTitle());
        $this->assertEquals('NQType', $entity->getTypeName());
        $this->assertEquals(false, $entity->getHasOwnDir());
        $this->assertEquals(false, $entity->getIsVisible());
        $this->assertEquals('2002-01-01', $entity->getDateCreated());
        $this->assertEquals('2002-02-02', $entity->getDatePublished());
        $this->assertEquals('2002-03-03', $entity->getDateModified());
        $this->assertEquals(2, $entity->getPriority());
    }

}

?>
