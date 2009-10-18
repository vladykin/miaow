<?php

// $Id$

require_once('../config.php');
require_once(LIB_DIR . '/Util.php');

require_once('PHPUnit/Framework.php');

/**
 * Test class for Util.
 */
class UtilTest extends PHPUnit_Framework_TestCase {

    function testIsValidName() {
        $this->assertTrue(Util::isValidName('x'));
        $this->assertTrue(Util::isValidName('_x_y303_'));
        $this->assertTrue(Util::isValidName('article.pdf'));
        $this->assertTrue(Util::isValidName('complex-name'));
        $this->assertFalse(Util::isValidName('!name'));
        $this->assertFalse(Util::isValidName('==name=='));
        $this->assertFalse(Util::isValidName('.'));
        $this->assertFalse(Util::isValidName('????'));
    }

    function testIsValidEmail() {
        $this->assertTrue(Util::isValidEmail('user@host.host.ru'));
        $this->assertTrue(Util::isValidEmail('user.user@host.ru'));
        $this->assertTrue(Util::isValidEmail('user-user@host-host.ru'));
        $this->assertFalse(Util::isValidEmail('user(at)mail(dot)ru'));
        $this->assertFalse(Util::isValidEmail('user @ host.ru'));
        $this->assertFalse(Util::isValidEmail('user@host'));
        $this->assertFalse(Util::isValidEmail('@host.ru'));
        $this->assertFalse(Util::isValidEmail('user@.ru'));
        $this->assertFalse(Util::isValidEmail('user@'));
    }

    function testPackArray() {
        $this->assertEquals(
            array(1, 2),
            Util::packArray(array(1 => 1, 2 => 2)));
        $this->assertEquals(
            array('x', 'y'),
            Util::packArray(array('a' => 'x', 'b' => 'y')));
    }

}

?>
