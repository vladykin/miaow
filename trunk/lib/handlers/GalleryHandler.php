<?php

// $Id$

require_once('lib/Handler.php');
require_once('lib/LinkTransformer.php');
require_once('lib/XhtmlParser.php');
require_once('lib/Properties.php');
require_once('lib/Templates.php');

class GalleryHandler implements Handler {

    /**
     * @access public
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
        foreach ($res as $row) {
            $imageNodes[] = $imageNode = EntityManager::decode($row, 'TreeNode');
            if ($imageNode->getName() == @$options['image']) {
                $currentImage = $imageNode;
            }
        }
        if (!isset($currentImage)) {
            $currentImage = $imageNodes[0];
        }
        $template = new SkinTemplate('gallery/main');
        $template->set('treePath', $treePath);
        $template->set('treeNode', $galleryNode);
        $template->set('imageNodes', $imageNodes);
        $template->set('currentImage', $currentImage);
        $template->fillAndPrint();
        return true;
    }

    /**
     * @access public abstract
     * @param TreePath $treePath
     * @return string
     */
    public function getPreview(TreePath $treePath, $options = array()) {
        $treeNode = $treePath->getNode();
        return '<a href="' . $treePath->toURL() . '">' . $treeNode->getTitle() . '</a>';
    }

    /**
     * @access public abstract
     * @param TreePath $treePath
     * @return array
     */
    public function getProperties(TreePath $treePath) {
        assert('is_a($treePath, \'TreePath\')');
        $treeNode = $treePath->getNode();
        return array(
            new TextProperty('Title', 'title', $treeNode->getTitle()),
            new FileProperty('File', 'file',
                    $treeNode->getProperty('file'),
                    $treePath->getDirectory()),
            new VisibilityProperty($treeNode->getIsVisible()),
            new UserListProperty('Authors', 'authors', array()),
            new KeywordListProperty('Keywords', 'keywords', array()),
        );
    }

}

?>
