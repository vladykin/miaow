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
        $template->set('action', '?action=saveEdited');
        $template->set('name', $article->getName());
        $template->set('title', $article->getTitle());
        $template->set('isVisible', $article->getIsVisible());
        $template->set('hasOwnDir', $article->getHasOwnDir());
        $template->fillAndPrint();
        return true;
    }

    public function handleSaveEdited(TreePath $treePath, $params = array()) {
        $article = $treePath->getNode();
        $article->setName((string)@$_POST['name']);
        $article->setTitle((string)@$_POST['title']);
        $article->setIsVisible(isset($_POST['isVisible']));
        $article->setProperty('file', $article->getHasOwnDir()?
                'index.html' : $article->getName() . '.html');
        $result = Tree::persistNode($article);
        HTTP::seeOther($treePath->toURL());
        return $result;
    }

    public function xhandleCreate(TreePath $treePath, $params = array()) {
        $template = new SkinTemplate('article/create');
        $template->set('typeName', 'Article');
        $template->set('action', '?action=saveCreated');
        $template->set('treePath', $treePath);
        $template->fillAndPrint();
        return true;
    }

    public function xhandleSaveCreated(TreePath $treePath, $params = array()) {
        $articleNode = new TreeNode();
        $articleNode->setName($_POST['name']);
        $articleNode->setTitle($_POST['title']);
        $articleNode->setTypeName($_POST['typeName']);
        $articleNode->setHasOwnDir(isset($_POST['hasOwnDir']));
        $articleNode->setIsVisible(isset($_POST['isVisible']));
        $articleNode->setProperty('file', $articleNode->getHasOwnDir()?
                'index.html' : $articleNode->getName() . '.html');
        $result = Tree::persistNode($articleNode, $treePath);
        HTTP::seeOther($treePath->toURL());
        return $result;
    }

}

?>
