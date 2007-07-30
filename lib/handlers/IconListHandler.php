<?php

// $Id$

require_once('lib/Handler.php');
require_once('lib/HandlerFactory.php');
require_once('lib/Util.php');
require_once('lib/XhtmlParser.php');
require_once('lib/Templates.php');


class IconListHandler extends Handler {

    public function handle(TreePath $treePath, $params = array()) {
        $db = Storage::getConnection();
        $treeNode = $treePath->getNode();

        $query = 'SELECT * FROM `!` WHERE `parentId` = ? AND (? || `isVisible`) ORDER BY `title`';
        $res = $db->getAll($query, array(
            TreeNode::getTableName(), 
            $treeNode->getId(), 
            Session::isPrivileged()
        ));
        assert('!DB::isError($res)');
        $children = array();
        foreach ($res as $row) {
            $child = array();
            $childNode = EntityManager::decode($row, 'TreeNode');
            $child['title'] = $childNode->getTitle();
            $child['href'] = $treePath->toURL() . '/' . $childNode->getName();
            $treePath->pushNode($childNode);
            $child['icon'] = SITE_URL . '/' . $treePath->getDirectory() . '/' . $childNode->getProperty('icon');
            $treePath->popNode();
            $children[] = $child;
        }

        $template = new SkinTemplate('iconlist/main');
        $template->set('treeNode', $treeNode);
        $template->set('treePath', $treePath);
        $template->set('children', $children);
        $template->fillAndPrint();
        return true;
    }

    public function handleEdit(TreePath $treePath, $params = array()) {
        $list = $treePath->getNode();
        $template = new SkinTemplate('iconlist/edit');
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
        $template = new SkinTemplate('iconlist/create');
        $template->set('typeName', 'IconList');
        $template->set('action', '?action=saveCreated');
        $template->set('basedir', $treePath->getDirectory());
        $template->fillAndPrint();
        return true;
    }

    public function xhandleSaveCreated(TreePath $treePath, $params = array()) {
        $listNode = new TreeNode();
        $listNode->setName($_POST['name']);
        $listNode->setTitle($_POST['title']);
        $listNode->setTypeName($_POST['typeName']);
        $listNode->setHasOwnDir(true);
        $listNode->setIsVisible(isset($_POST['isVisible']));
        $listNode->setProperty('icon', 'icon.jpg');
        $result = Tree::persistNode($listNode, $treePath);
        HTTP::seeOther($treePath->toURL());
        return $result;
    }

}

?>
