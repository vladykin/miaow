<?php

// $Id$

require_once('config.php');

require_once('lib/Users.php');
require_once('lib/Templates.php');

session_start();

$user = Users::getCurrentUser();
assert('$user instanceof User');

Users::requireLogin($user);

$template = new LayoutTemplate('admin');
$template->set('title', 'Admin panel');
$template->set('content', new ContentTemplate('admin_panel'));
$template->fillAndPrint();

?>
