<?php

// $Id$

require_once('lib/Handler.php');

class ImageHandler extends Handler {

    public function handle(TreePath $treePath, $params = array()) {
        $treeNode = $treePath->popNode();
        $imageName = $treeNode->getName();
        $handler = HandlerFactory::getHandler('Gallery');
        $handler->handle($treePath, array('image' => $imageName));
        return true;
    }

    public function handleEdit(TreePath $treePath, $params = array()) {
        $imageNode = $treePath->getNode();
        $template = new SkinTemplate('gallery/edit_image');
        $template->set('typeName', 'Image');
        $template->set('action', '?action=saveEdited');
        $template->set('dir', $treePath->toString());
        $template->set('name', $imageNode->getName());
        $template->set('title', $imageNode->getTitle());
        $template->set('isVisible', $imageNode->getIsVisible());
        $template->set('authors', $imageNode->getProperty('authors'));
        $template->fillAndPrint();
        return true;
    }

    public function handleSaveEdited(TreePath $treePath, $params = array()) {
        $imageNode = $treePath->getNode();
        $imageNode->setName($_POST['name']);
        $imageNode->setTitle($_POST['title']);
        $imageNode->setIsVisible(isset($_POST['isVisible']));
        $imageNode->setProperty('src', $imageNode->getName() . '.jpg');
        $imageNode->setProperty('icon', $imageNode->getName() . 's.jpg');
        $imageNode->setProperty('authors', $_POST['authors']);
        Tree::persistNode($imageNode);
        HTTP::seeOther($treePath->toURL());
        return true;
    }

    public function xhandleCreate(TreePath $treePath, $params = array()) {
        $template = new SkinTemplate('gallery/create_image');
        $template->set('typeName', 'Image');
        $template->set('action', '?action=saveCreated');
        $template->set('dir', $treePath->toString());
        $template->fillAndPrint();
        return true;
    }

    public function xhandleSaveCreated(TreePath $treePath, $params = array()) {
        $imageNode = new TreeNode();
        $imageNode->setName($_POST['name']);
        $imageNode->setTitle(strlen($_POST['title'])? $_POST['title'] : $_POST['name']);
        $imageNode->setTypeName($_POST['typeName']);
        $imageNode->setHasOwnDir(false);
        $imageNode->setIsVisible(isset($_POST['isVisible']));
        $imageNode->setProperty('src', $imageNode->getName() . '.jpg');
        $imageNode->setProperty('icon', $imageNode->getName() . 's.jpg');
        $imageNode->setProperty('authors', $_POST['authors']);
        Tree::persistNode($imageNode, $treePath);
        HTTP::seeOther($treePath->toURL());
        return true;
    }

}

?>
