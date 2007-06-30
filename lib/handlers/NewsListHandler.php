<?php

// $Id$

require_once('lib/Handler.php');
require_once('lib/HandlerFactory.php');
require_once('lib/Util.php');
require_once('lib/XhtmlParser.php');
require_once('lib/Templates.php');


class NewsListHandler extends Handler {

    public function handle(TreePath $treePath, $params = array()) {
        $treeNode = $treePath->getNode();
        $db = Storage::getConnection();
        $query = 'SELECT DISTINCT YEAR(`datePublished`) FROM `!` WHERE `parentId` = ? AND (? || `isVisible`) ORDER BY `datePublished` DESC';
        $years = $db->getAll($query, array(TreeNode::getTableName(), $treeNode->getId(), Session::isPrivileged()));
        assert('!DB::isError($years)');
        foreach ($years as &$value) {
            $value = $value[0];
            if ($value == (int)@$_GET['year']) {
                $year = $value;
            }
        }
        if (!isset($year)) {
            $year = $years[0];
        }
        $query = 'SELECT * FROM `!` WHERE `parentId` = ? AND (? || `isVisible`) AND YEAR(`datePublished`) = ? ORDER BY `dateCreated` DESC';
        $allNews = $db->getAll($query, array(
            TreeNode::getTableName(), 
            $treeNode->getId(), 
            Session::isPrivileged(),
            $year
        ));
        assert('!DB::isError($allNews)');
        foreach ($allNews as &$news) {
            $tmp = EntityManager::decode($news, 'TreeNode');
            $handler = HandlerFactory::getHandler($tmp->getTypeName());
            $treePath->pushNode($tmp);
            $news = $handler->getPreview($treePath);
            $treePath->popNode();
        }
        $template = new SkinTemplate('news/list');
        $template->set('treeNode', $treeNode);
        $template->set('treePath', $treePath);
        $template->set('allNews', $allNews);
        $template->set('years', $years);
        $template->set('currentYear', $year);
        $template->fillAndPrint();
        return true;
    }

    public function handleEdit(TreePath $treePath, $params = array()) {
        $newslist = $treePath->getNode();
        $template = new SkinTemplate('news/list_edit');
        $template->set('treePath', $treePath);
        $template->set('article', $newslist);
        $template->set('action', '?action=editSave');
        $template->set('title', $newslist->getTitle());
        $template->set('isVisible', $newslist->getIsVisible());
        $template->fillAndPrint();
        return true;
    }

    public function handleSaveEdited(TreePath $treePath, $params = array()) {
        $newslist = $treePath->getNode();
        $newslist->setTitle((string)@$_POST['title']);
        $newslist->setIsVisible((bool)@$_POST['isVisible']);
        $result = Tree::persistNode($newslist);
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
