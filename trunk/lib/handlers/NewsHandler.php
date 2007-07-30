<?php

/**
 * $Id$
 */

require_once('lib/Handler.php');
require_once('lib/LinkTransformer.php');
require_once('lib/TextParser.php');
require_once('lib/Templates.php');


class NewsHandler extends Handler {

    /**
     * @param TreePath $treePath
     * @return boolean
     */
    public function handle(TreePath $treePath, $params = array()) {
        $treeNode = $treePath->getNode();
        $directory = $treePath->getDirectory();
//        $file = $treeNode->getProperty('file');
        $transformer = new LinkTransformer(TREE_ROOT, '?');
//        if ($file) {
//            $parser = new XhtmlParser();
//            $parser->parseFile($file, $transformer);
//            $content = $parser->getContent();
//        } else {
            $parser = new TextParser();
            $content = $parser->parse($treeNode->getProperty('text'), $transformer);
//        }
        $template = new SkinTemplate('news/main');
        $template->set('treePath', $treePath);
        $template->set('treeNode', $treeNode);
        $template->set('content', $content);
        $template->fillAndPrint();
        return true;
    }
    
    /**
     * @param TreePath $treePath
     * @return string
     */
    public function getPreview(TreePath $treePath, $params = array()) {
        $treeNode = $treePath->getNode();
        $parser = new TextParser();
        $transformer = new LinkTransformer(SITE_URL . '/index.php', '?');
        $t = new SkinTemplate('news/preview');
        $t->set('treePath', $treePath);
        $t->set('content', $parser->parse($treeNode->getProperty('text'), $transformer));
        return $t->fillAndReturn();
    }

    public function handleEdit(TreePath $treePath, $params = array()) {
        $news = $treePath->getNode();
        $template = new SkinTemplate('news/edit');
        $template->set('treePath', $treePath);
        $template->set('action', '?action=saveEdited');
        $template->set('name', $news->getName());
        $template->set('title', $news->getTitle());
        $template->set('isVisible', $news->getIsVisible());
        $template->set('text', $news->getProperty('text'));
        $template->fillAndPrint();
        return true;
    }

    public function handleSaveEdited(TreePath $treePath, $params = array()) {
        $news = $treePath->getNode();
        $news->setName((string)@$_POST['name']);
        $news->setTitle((string)@$_POST['title']);
        $news->setIsVisible(isset($_POST['isVisible']));
        $news->setProperty('text', (string)@$_POST['text']);
//        $news->setProperty('file', (string)@$_POST['file']);
        $result = Tree::persistNode($news);
        $treePath->popNode();
        HTTP::seeOther($treePath->toURL());
        $treePath->pushNode($news);
        return $result;
    }

    public function xhandleCreate(TreePath $treePath, $params = array()) {
        $template = new SkinTemplate('news/create');
        $template->set('typeName', 'News');
        $template->set('action', '?action=saveCreated');
        $template->fillAndPrint();
        return true;
    }

    public function xhandleSaveCreated(TreePath $treePath, $params = array()) {
        $news = new TreeNode();
        $news->setName($_POST['name']);
        $news->setTitle($_POST['title']);
        $news->setTypeName($_POST['typeName']);
        $news->setHasOwnDir(true);
        $news->setIsVisible(isset($_POST['isVisible']));
        $news->setProperty('text', $_POST['text']);
        $result = Tree::persistNode($news, $treePath);
        HTTP::seeOther($treePath->toURL());
        return $result;
    }

}

?>
