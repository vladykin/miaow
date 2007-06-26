<?php

// $Id$

require_once('lib/Handler.php');

class ImageHandler extends DefaultHandler {

    public function handle(TreePath $treePath, $options = array()) {
        $treeNode = $treePath->popNode();
        $imageName = $treeNode->getName();
        $handler = HandlerFactory::getHandler('Gallery');
        $handler->handle($treePath, array('image' => $imageName));
        return true;
    }

    public function getPreview(TreePath $treePath, $options = array()) {
        $treeNode = $treePath->getNode();
        return '<a href="' . $treePath->toURL() . '">' . $treeNode->getTitle() . '</a>'; 
    }

    public function getProperties(TreePath $treePath) {
    }

}

?>
