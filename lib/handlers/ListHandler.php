<?php

// $Id$

require_once('lib/Handler.php');
require_once('lib/HandlerFactory.php');
require_once('lib/Util.php');
require_once('lib/XhtmlParser.php');
require_once('lib/Templates.php');


class ListHandler extends Handler {

    public function handle(TreePath $treePath, $params = array()) {
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

    public function handleEdit(TreePath $treePath, $params = array()) {
        $list = $treePath->getNode();
        $template = new SkinTemplate('list/edit');
        $template->set('treePath', $treePath);
        $template->set('list', $list);
        $template->set('action', '?action=saveEdited');
        $template->set('title', $list->getTitle());
        $template->set('isVisible', $list->getIsVisible());
        $template->set('order', $list->getProperty('order'));
        $template->fillAndPrint();
        return true;
    }

    public function handleSaveEdited(TreePath $treePath, $params = array()) {
        $list = $treePath->getNode();
        $list->setTitle((string)@$_POST['title']);
        $list->setIsVisible((bool)@$_POST['isVisible']);
        $list->setProperty('order', (string)@$_POST['order']);
        $result = Tree::persistNode($list);
        HTTP::seeOther($treePath->toURL());
        return $result;
    }

    public function xhandleCreate(TreePath $treePath, $params = array()) {
        return false;
    }

    public function xhandleSaveCreated(TreePath $treePath, $params = array()) {
        return false;
    }

}

?>
