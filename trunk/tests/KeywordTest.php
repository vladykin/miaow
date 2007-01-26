<?php

// $Id$

require_once('PHPUnit/TestCase.php');

require_once('lib/Keyword.php');

/**
 * Test class for Keyword.
 */
class KeywordTest extends PHPUnit_TestCase {

    function setUp() {
        EntityManager::installEntityClass('Keyword');
    }

    function tearDown() {
        EntityManager::uninstallEntityClass('Keyword');
    }

    function testPersistFindRemove() {
        // create and persist a keyword
        $entity =& new Keyword();
        $this->assertEquals(0, $entity->getId());
        $entity->setURI('http://domain/PersistFindRemove');
        $entity->setTitle('PersistFindRemove');
        EntityManager::persist($entity);
        $id = $entity->getId();
        $this->assertTrue($entity->getId() > 0);
        // now try to get it back
        $entity =& EntityManager::find('Keyword', $id);
        $this->assertNotNull($entity);
        $this->assertEquals($id, $entity->getId());
        $this->assertEquals('http://domain/PersistFindRemove', $entity->getURI());
        $this->assertEquals('PersistFindRemove', $entity->getTitle());
        // remove keyword from storage
        EntityManager::remove($entity);
        // check that it has been removed
        $entity =& EntityManager::find('Keyword', $id);
        $this->assertNull($entity);
    }

    function testUpdate() {
        // create and persist a keyword
        $entity =& new Keyword();
        $entity->setURI('http://domain/Update');
        $entity->setTitle('Update');
        EntityManager::persist($entity);
        $id = $entity->getId();
        // modify properties and persist it again
        $entity->setURI('http://domain/Update(Modified)');
        $entity->setTitle('Update(Modified)');
        EntityManager::persist($entity);
        // get it back and verify updates
        $entity =& EntityManager::find('Keyword', $id);
        $this->assertEquals('http://domain/Update(Modified)', $entity->getURI());
        $this->assertEquals('Update(Modified)', $entity->getTitle());
    }

    function testNativeQuery() {
        // create and persist a keyword
        $entity =& new Keyword();
        $entity->setURI('http://domain/NativeQuery1');
        $entity->setTitle('NativeQuery1');
        EntityManager::persist($entity);
        $id = $entity->getId();
        // create and persist another keyword
        $entity =& new Keyword();
        $entity->setURI('http://domain/NativeQuery2');
        $entity->setTitle('NativeQuery2');
        EntityManager::persist($entity);
        // now issue a native query to storage
        $db =& Storage::getConnection();
        $query = 'SELECT * FROM `!` WHERE `title` = ?';
        $row =& $db->getRow($query, array(
            Keyword::getTableName(), 'NativeQuery1'));
        $this->assertType('array', $row);
        $entity =& EntityManager::decode($row, 'Keyword');
        // check that we've got expected result
        $this->assertEquals($id, $entity->getId());
        $this->assertEquals('http://domain/NativeQuery1', $entity->getURI());
        $this->assertEquals('NativeQuery1', $entity->getTitle());
    }

}

?>
