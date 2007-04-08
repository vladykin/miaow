<?php

// $Id$

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
    $template =& new PageTemplate('admin', 'node_edit', array(
        'action' => SITE_URL . '/node_edit.php/' . $treePath->toString(),
        'properties' => $properties
    ));
    $template->set('title', 'Edit');
    $template->fillAndPrint();
} else {
    foreach ($properties as &$property) {
        $result = $property->parseValue($_POST[$property->getName()]);
        //assert('$result === true');
        switch ($property->getName()) {
        case 'name':
            $treeNode->setName($property->getValue());
            break;
        case 'title':
            $treeNode->setTitle($property->getValue());
            break;
        case 'typeName':
            $treeNode->setTypeName($property->getValue());
            break;
        case 'hasOwnDir':
            $treeNode->setHasOwnDir($property->getValue());
            break;
        default:
            $treeNode->setProperty($property->getName(), $property->getValue());
        }
    }
    assert('Tree::persistNode($treeNode) === true');
    HTTP::seeOther(SITE_URL . '/index.php/' . $treePath->toString());
}

?>
