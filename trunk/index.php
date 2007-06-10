<?php

// $Id$

require_once('config.php');
require_once('lib/Tree.php');
require_once('lib/HandlerFactory.php');
require_once('lib/HTTP.php');
require_once('lib/Users.php');

session_start();

$user =& Users::getCurrentUser();
assert('is_a($user, \'User\')');

$pathInfo = strval(@$_SERVER['PATH_INFO']);
$treePath =& Tree::resolvePath($pathInfo);
if ($treePath == null) {
    HTTP::notFound();
    exit(1);
}

$node =& $treePath->getNode();

if (!$node->getIsVisible() && !$user->isAdmin()) {
    HTTP::forbidden();
    exit(1);
}

$handler =& HandlerFactory::getHandler($node->getTypeName());
if (!$handler->handle($treePath, array())) {
    HTTP::internalServerError();
    exit(1);
}

?>
