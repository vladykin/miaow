<?php

// $Id$

define('TEST', true);
require_once('lib/Config.php');

require_once('PHPUnit.php');
$suite =& new PHPUnit_TestSuite();

require_once('tests/UtilTest.php');
$suite->addTestSuite('UtilTest');

require_once('tests/CacheTest.php');
$suite->addTestSuite('CacheTest');

require_once('tests/KeywordTest.php');
$suite->addTestSuite('KeywordTest');

require_once('tests/UserTest.php');
$suite->addTestSuite('UserTest');

require_once('tests/TreeNodeTest.php');
$suite->addTestSuite('TreeNodeTest');

require_once('tests/TreeTest.php');
$suite->addTestSuite('TreeTest');

header('Content-type: text/plain'); 
$result =& PHPUnit::run($suite);
echo($result->toString());

?>
