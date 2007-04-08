<?php

// $Id$

define('TEST', true);
require_once('config.php');

require_once('PHPUnit.php');
require_once('PHPUnit/GUI/HTML.php');
require_once('PHPUnit/GUI/SetupDecorator.php');

$gui =& new PHPUnit_GUI_SetupDecorator(new PHPUnit_GUI_HTML());
$gui->getSuitesFromDir('tests', '.*\.php$');
$gui->show(true);

?>
