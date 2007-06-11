<?php

// $Id$

require_once('lib/Handler.php');

class ImageHandler implements Handler {

    public function handle(TreePath $treePath, $options = array()) {
        $treeNode = $treePath->popNode();
        $imageName = $treeNode->getName();
        $handler = HandlerFactory::getHandler('Gallery');
        $handler->handle($treePath, array('image' => $imageName));
        return true;
    }

    public function getPreview(TreePath $treePath, $options = array()) {
        assert('is_a($treePath, \'TreePath\')');
        $treeNode = $treePath->getNode();
        return '<a href="' . $treePath->toURL() . '">' . $treeNode->getTitle() . '</a>'; 
    }

    public function getProperties(TreePath $treePath) {
    }

}

?>
