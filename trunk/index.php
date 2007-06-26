<?php

// $Id$

require_once('config.php');
require_once('lib/Tree.php');
require_once('lib/HandlerFactory.php');
require_once('lib/HTTP.php');
require_once('lib/Session.php');

$pathInfo = strval(@$_SERVER['PATH_INFO']);
$treePath = Tree::resolvePath($pathInfo);
if ($treePath == null) {
    HTTP::notFound();
    exit(1);
}

$node = $treePath->getNode();

if (!$node->getIsVisible() && !Session::isPrivileged()) {
    HTTP::forbidden();
    exit(1);
}

$action = strval(@$_GET['action']);

if ($action) {
    Session::ensurePrivileged();
}

$handler = HandlerFactory::getHandler($node->getTypeName());
$method = 'handle' . ucfirst($action);
if (!$handler->$method($treePath)) {
    HTTP::internalServerError();
    exit(1);
}

?>
