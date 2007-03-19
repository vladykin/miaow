<?php

// $Id$

require_once('config.php');

require_once('lib/Storage.php');
require_once('lib/Tree.php');
require_once('lib/HandlerFactory.php');
require_once('lib/Users.php');
require_once('lib/HTTP.php');
require_once('lib/Properties.php');
require_once('lib/Templates.php');

session_start();

$user = Users::getCurrentUser();
assert('$user instanceof User');

Users::requireLogin($user);

$treePath = Tree::resolvePath(strval(@$_SERVER['PATH_INFO']));
if ($treePath === null) {
    HTTP::notFound();
    exit(1);
}

if (empty($_POST)) {
    showPage1();
} elseif ('1' === @$_POST['page']) {
    $node = new TreeNode();
    $node->setName($_POST['name']);
    $node->setTypeName($_POST['typeName']);
    $node->setHasOwnDir($_POST['hasOwnDir']);
    $treePath->pushNode($node);
    showPage2();
} elseif ('2' === @$_POST['page']) {
    if (problem) {
        showPage2('problem description');
    } else {
        Tree::persistNode($node, $treePath);
    }
} else {
//  something went wrong
}

function showPage1($message = null) {
    global $treePath;
    $template = new PageTemplate('minimal', 'node_create_page_1', array(
        'action' => SITE_URL . '/node_create.php/' . $treePath->toString(),
        'properties' => array(
            new TextProperty('Name', 'name', ''),
            new OwnDirectoryProperty(true),
            new SelectProperty('Type', 'typeName', 'Article', HandlerFactory::getKnownTypes())
        )
    ));
    $template->fillAndPrint();    
}

function showPage2($message = null) {
    global $treePath;
    $knownTypes = HandlerFactory::getKnownTypes();
    $handler = HandlerFactory::getHandler($knownTypes[(int)$_POST['typeName']]);
    $template = new PageTemplate('minimal', 'node_create_page_2', array(
        'action' => SITE_URL . '/node_create.php/' . $treePath->toString(),
        'properties' => $handler->getProperties($treePath)
    ));
    $template->fillAndPrint();
}

exit(1);



$properties = array(
    new TextProperty('Name', 'name', ''),
    new OwnDirectoryProperty(true),
    new SelectProperty('Type', 'typeName', 'Article', HandlerFactory::getKnownTypes())
);

if (empty($_POST)) {
    $template = new PageTemplate('minimal', 'admin_form', array(
        'action' => SITE_URL . '/node_create.php/' . $treePath->toString(),
        'properties' => $properties 
    ));
    $template->fillAndPrint();
} else {
    $node = new TreeNode();
    $node->setTitle('notitle');
    $node->setDatePublished(date('Y-m-d'));
    $node->setDateModified(date('Y-m-d'));
    $node->setIsVisible(false);
    $node->setPriority(0);
    for ($i = 0; $i < count($properties); ++$i) {
        $result = $properties[$i]->parseValue($_POST[$properties[$i]->getName()]);
//        assert('$result === true');
        switch ($properties[$i]->getName()) {
        case 'name':
            $node->setName($properties[$i]->getValue());
            break;
        case 'typeName':
            $node->setTypeName($properties[$i]->getValue());
            break;
        case 'hasOwnDir':
            $node->setHasOwnDir($properties[$i]->getValue());
            break;
        default:
            $node->setProperty($properties[$i]->getName(), $properties[$i]->getValue());
        }
    }
    $res = Tree::persistNode($node, $treePath);
    assert('$res === true');
    $treePath->pushNode($node);
    HTTP::seeOther(SITE_URL . '/node_edit.php/' . $treePath->toString());
}

?>
