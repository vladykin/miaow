<?php

// $Id$

require_once('lib/Handler.php');
require_once('lib/LinkTransformer.php');
require_once('lib/XhtmlParser.php');
require_once('lib/Templates.php');

class ArticleHandler extends Handler {

    /**
     * @param TreePath $treePath
     * @param array $params
     * @return boolean
     */
    public function handle(TreePath $treePath, $params = array()) {
        $treeNode = $treePath->getNode();
        $dir = $treePath->getDirectory();
        $parser = new XhtmlParser();
        $transformer = new LinkTransformer(SITE_URL . '/index.php', SITE_URL . '/' . $dir);
        $parser->parseFile($dir . '/' . $treeNode->getProperty('file'), $transformer);
        $template = new SkinTemplate('article/main');
        $template->set('treePath', $treePath);
        $template->set('treeNode', $treeNode);
        $template->set('content', $parser->getContent());
        $template->set('authors', $treeNode->getAuthors());
        if ($treeNode->getProperty('listChildren') || Session::isPrivileged()) {
            $db = Storage::getConnection();
            $query = 'SELECT * FROM ! WHERE parentId = ? AND (isVisible || ?)';
            $res = $db->getAll($query, array(TreeNode::getTableName(), $treeNode->getId(), Session::isPrivileged()));
            assert('!DB::isError($res)');
            $children = array();
            foreach ($res as $row) {
                $childNode = EntityManager::decode($row, 'TreeNode');
                $handler = HandlerFactory::getHandler($childNode->getTypeName());
                $treePath->pushNode($childNode);
                $children[] = $handler->getPreview($treePath);
                $treePath->popNode();
            }
            $template->set('children', $children);
        }
        $template->fillAndPrint();
        return true;
    }

    public function handleEdit(TreePath $treePath, $params = array()) {
        $article = $treePath->getNode();
        $template = new SkinTemplate('article/edit');
        $template->set('treePath', $treePath);
        $template->set('article', $article);
        $template->set('action', '?action=saveEdited');
        $template->set('title', $article->getTitle());
        $template->set('isVisible', $article->getIsVisible());
        $template->set('file', $article->getProperty('file'));
        $template->fillAndPrint();
        return true;
    }

    public function handleSaveEdited(TreePath $treePath, $params = array()) {
        $article = $treePath->getNode();
        $article->setTitle((string)@$_POST['title']);
        $article->setIsVisible(isset($_POST['isVisible']));
        $article->setProperty('file', (string)@$_POST['file']);
        $result = Tree::persistNode($article);
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
