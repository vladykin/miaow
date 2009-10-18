<?php

// $Id$

require_once('../config.php');
require_once(LIB_DIR . '/User.php');

require_once('PHPUnit/Framework.php');

/**
 * Test class for User.
 */
class UserTest extends PHPUnit_Framework_TestCase {

    function setUp() {
        EntityManager::installEntityClass('User');
    }

    function tearDown() {
        EntityManager::uninstallEntityClass('User');
    }

    function testPersistFindRemove() {
        // create and persist a user
        $entity = new User();
        $this->assertEquals(0, $entity->getId());
        $entity->setName('PFR User');
        $entity->setEmail('pfr@user.com');
        $entity->setPassword('pfrpfrpfr');
        EntityManager::persist($entity);
        $id = $entity->getId();
        $this->assertTrue($entity->getId() > 0);
        // now try to get it back
        $entity = EntityManager::find('User', $id);
        $this->assertNotNull($entity);
        $this->assertEquals($id, $entity->getId());
        $this->assertEquals('PFR User', $entity->getName());
        $this->assertEquals('pfr@user.com', $entity->getEmail());
        $this->assertEquals('pfrpfrpfr', $entity->getPassword());
        // remove user from storage
        EntityManager::remove($entity);
        // check that it has been removed
        $entity = EntityManager::find('User', $id);
        $this->assertNull($entity);
    }

    function testUpdate() {
        // create and persist a user
        $entity = new User();
        $entity->setName('U User');
        $entity->setEmail('u@user.com');
        $entity->setPassword('uuuuuu');
        EntityManager::persist($entity);
        $id = $entity->getId();
        // modify properties and persist it again
        $entity->setName('UU User');
        $entity->setEmail('uu@user.com');
        $entity->setPassword('uuuuuuu');
        EntityManager::persist($entity);
        // get it back and verify updates
        $entity = EntityManager::find('User', $id);
        $this->assertEquals('UU User', $entity->getName());
        $this->assertEquals('uu@user.com', $entity->getEmail());
        $this->assertEquals('uuuuuuu', $entity->getPassword());
    }

    function testNativeQuery() {
        // create and persist a user
        $entity = new User();
        $entity->setName('NQ User 1');
        $entity->setEmail('nq1@user.com');
        $entity->setPassword('nq1nq1');
        EntityManager::persist($entity);
        $id = $entity->getId();
        // create and persist another user
        $entity = new User();
        $entity->setName('NQ User 2');
        $entity->setEmail('nq2@user.com');
        $entity->setPassword('nq2nq2');
        EntityManager::persist($entity);
        // now issue a native query to storage
        $db = Storage::getConnection();
        $query = 'SELECT * FROM `!` WHERE `password` = ?';
        $row = $db->getRow($query, array(
            User::getTableName(), 'nq1nq1'));
        $this->assertType('array', $row);
        $entity = EntityManager::decode($row, 'User');
        // check that we've got expected result
        $this->assertEquals($id, $entity->getId());
        $this->assertEquals('NQ User 1', $entity->getName());
        $this->assertEquals('nq1@user.com', $entity->getEmail());
        $this->assertEquals('nq1nq1', $entity->getPassword());
    }

}

?>
