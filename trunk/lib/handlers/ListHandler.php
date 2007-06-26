<?php

// $Id$

require_once('lib/Handler.php');
require_once('lib/HandlerFactory.php');
require_once('lib/Util.php');
require_once('lib/XhtmlParser.php');
require_once('lib/Properties.php');
require_once('lib/Templates.php');


class ListHandler extends DefaultHandler {

    public function handle(TreePath $treePath, $options = array()) {
        $db = Storage::getConnection();
        $treeNode = $treePath->getNode();

        $query = 'SELECT COUNT(*) FROM `!` WHERE `parentId` = ? AND (? || `isVisible`)';
        $res = $db->getOne($query, array(TreeNode::getTableName(), $treeNode->getId(), 1/*$user->isAdmin()*/));
        assert('!DB::isError($res)');
        $childNodeCount = $res;

        $childNodeIndex = max(0, min((int) @$_GET['f'], $childNodeCount - 10));

        $query = 'SELECT * FROM `!` WHERE `parentId` = ? AND (? || `isVisible`) ORDER BY !';
        $query = $db->modifyLimitQuery($query, $childNodeIndex, 10);
        $res = $db->getAll($query, array(
            TreeNode::getTableName(), 
            $treeNode->getId(), 
            Session::isPrivileged(),
            $treeNode->getProperty('order')
        ));
        assert('!DB::isError($res)');
        $childPreviews = array();
        foreach ($res as $row) {
            $childNode = EntityManager::decode($row, 'TreeNode');
            $handler = HandlerFactory::getHandler($childNode->getTypeName());
            $treePath->pushNode($childNode);
            $childPreviews[] = $handler->getPreview($treePath);
            $treePath->popNode();
        }

        $template = new SkinTemplate('list/main');
        $template->set('treeNode', $treeNode);
        $template->set('treePath', $treePath);
        $template->set('childCount', $childNodeCount);
        $template->set('childIndex', $childNodeIndex);
        $template->set('childPreviews', $childPreviews);
        $template->fillAndPrint();
        return true;
    }

    public function getPreview(TreePath $treePath, $options = array()) {
        $treeNode = $treePath->getNode();
        return '<a href="' . $treePath->toURL() . '">' . $treeNode->getTitle() . '</a>';
    }

    public function getProperties(TreePath $treePath) {
        $treeNode = $treePath->getNode();
        return array(
            new TextProperty('Title', 'title', $treeNode->getTitle()),
            new OrderProperty('Order by', 'order', $treeNode->getProperty('order')),
            new VisibilityProperty($treeNode->getIsVisible())
        );
    }

}

?>
