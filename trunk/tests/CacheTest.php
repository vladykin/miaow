<?php

// $Id$

require_once('PHPUnit/TestCase.php');

require_once('lib/Cache.php');

/**
 * Test class for Cache.
 */
class tests_CacheTest extends PHPUnit_TestCase {

    function setUp() {
        Cache::install();
    }

    function tearDown() {
        Cache::uninstall();
    }

    function testPutGetClear() {
        // put some data to cache
        Cache::put('asdf', 'qwer');
        Cache::put('zxcv', 987);
        // now try to get it back
        $this->assertEquals('qwer', Cache::get('asdf'));
        $this->assertEquals(987, Cache::get('zxcv'));
        // clear the cache
        Cache::clear();
        // check that it has been cleared
        $this->assertNull(Cache::get('asdf'));
        $this->assertNull(Cache::get('zxcv'));
    }

    function testUpdate() {
        // put some data to cache
        Cache::put('key', 'data');
        $this->assertEquals('data', Cache::get('key'));
        // bind different data to same key
        Cache::put('key', 'other-data');
        $this->assertEquals('other-data', Cache::get('key'));
        // cleanup
        Cache::clear();
    }

    function testPutRemove() {
        // put some data to cache
        Cache::put('asdf', 'qwer');
        Cache::put('zxcv', 'uiop');
        // remove one piece of data
        Cache::put('asdf', null);
        // verify removal
        $this->assertNull(Cache::get('asdf'));
        $this->assertNotNull(Cache::get('zxcv'));
        // cleanup
        Cache::clear();
    }


}

?>
