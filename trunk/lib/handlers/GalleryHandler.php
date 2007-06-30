<?php

// $Id$

require_once('lib/Handler.php');
require_once('lib/LinkTransformer.php');
require_once('lib/XhtmlParser.php');
require_once('lib/Templates.php');

class GalleryHandler extends Handler {

    /**
     * @param TreePath $treePath
     * @return boolean
     */
    public function handle(TreePath $treePath, $options = array()) {
        $galleryNode = $treePath->getNode();
        $db = Storage::getConnection();
        $query = 'SELECT * FROM ! WHERE parentId = ? AND (isVisible || ?) ORDER BY name';
        $res = $db->getAll($query, array(TreeNode::getTableName(), $galleryNode->getId(), 1/*$user->isAdmin()*/));
        assert('!DB::isError($res)');
        $imageNodes = array();
        $i = 0;
        foreach ($res as $row) {
            $imageNodes[] = $imageNode = EntityManager::decode($row, 'TreeNode');
            if ($imageNode->getName() == @$options['image']) {
                $currentImage = $imageNode;
                $currentIndex = $i;
            }
            ++$i;
        }
        if (!isset($currentImage) && count($imageNodes) > 0) {
            $currentIndex = 0;
            $currentImage = $imageNodes[0];
        }
        $query = 'SELECT * FROM ! WHERE parentId = ? AND (isVisible || ?) ORDER BY title';
        $res = $db->getAll($query, array(TreeNode::getTableName(), $galleryNode->getParentId(), Session::isPrivileged()));
        assert('!DB::isError($res)');
        $leftLinks = array();
        $treePath->popNode();
        foreach ($res as $row) {
            $leftLink = array();
            $anotherGalleryNode = EntityManager::decode($row, 'TreeNode');
            $leftLink['title'] = $anotherGalleryNode->getTitle();
            $leftLink['selected'] = $anotherGalleryNode->getName() == $galleryNode->getName();
            $treePath->pushNode($anotherGalleryNode);
            $leftLink['href'] = $treePath->toURL();
            $treePath->popNode();
            $leftLinks[] = $leftLink;
        }
        $treePath->pushNode($galleryNode);
        $template = new SkinTemplate('gallery/main');
        $template->set('treePath', $treePath);
        $template->set('treeNode', $galleryNode);
        $template->set('imageNodes', $imageNodes);
        $template->set('currentImage', @$currentImage);
        $template->set('currentIndex', @$currentIndex);
        $template->set('leftLinks', $leftLinks);
        $template->fillAndPrint();
        return true;
    }

    public function handleEdit(TreePath $treePath, $params = array()) {
        return false;
    }

    public function handleSaveEdited(TreePath $treePath, $params = array()) {
        return false;
    }

    public function handleCreate(TreePath $treePath, $params = array()) {
        $handler = HandlerFactory::getHandler('Image');
        return $handler->xhandleCreate($treePath, $params);
    }

    public function handleSaveCreated(TreePath $treePath, $params = array()) {
        $handler = HandlerFactory::getHandler('Image');
        return $handler->xhandleSaveCreated($treePath, $params);
    }

    public function xhandleCreate(TreePath $treePath, $params = array()) {
        return false;
    }

    public function xhandleSaveCreated(TreePath $treePath, $params = array()) {
        return false;
    }

}

?>
