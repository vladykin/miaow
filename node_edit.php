<?php

require_once('config.php');

require_once('lib/Storage.php');
require_once('lib/Tree.php');
require_once('lib/HandlerFactory.php');
require_once('lib/Users.php');
require_once('lib/HTTP.php');
require_once('lib/Templates.php');

session_start();

$user = Users::getCurrentUser();
assert('is_a($user, \'User\')');

Users::requireLogin($user);

$treePath = Tree::resolvePath(strval(@$_SERVER['PATH_INFO']));
if ($treePath === null) {
    HTTP::notFound();
    exit(1);
}

$treeNode =& $treePath->getNode();
$handler =& HandlerFactory::getHandler($treeNode->getTypeName());
$properties =& $handler->getProperties($treePath);

if (empty($_POST)) {
    $template =& new PageTemplate('minimal', 'admin_form', array(
        'action' => SITE_URL . '/node_edit.php/' . $treePath->toString(),
        'properties' => $properties
    ));
    $template->set('title', 'Edit');
    $template->fillAndPrint();
} else {
    for ($i = 0; $i < count($properties); ++$i) {
        $property =& $properties[$i];
        $result = $property->parseValue($_POST[$property->getName()]);
        //assert('$result === true');
        $treeNode->setProperty($property->getName(), $property->getValue());
    }
    Tree::persistNode($treeNode);
    HTTP::seeOther(SITE_URL . '/index.php/' . $treePath->toString());
}

?>
