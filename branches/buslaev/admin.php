<?php

// $Id$

require_once('config.php');

require_once('lib/Session.php');
require_once('lib/Templates.php');

Session::ensurePrivileged();

$template = new SkinTemplate('admin/main');
$template->fillAndPrint();

?>
