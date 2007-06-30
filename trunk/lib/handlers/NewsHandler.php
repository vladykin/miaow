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
        $file = $treeNode->getProperty('file');
        $transformer = new LinkTransformer(TREE_ROOT, '?');
        if ($file) {
            $parser = new XhtmlParser();
            $parser->parseFile($file, $transformer);
            $content = $parser->getContent();
        } else {
            $parser = new TextParser();
            $content = $parser->parse($treeNode->getProperty('preview'), $transformer);
        }
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
        $t->set('content', $parser->parse($treeNode->getProperty('preview'), $transformer));
        return $t->fillAndReturn();
    }

    public function handleEdit(TreePath $treePath, $params = array()) {
        $news = $treePath->getNode();
        $template = new SkinTemplate('news/edit');
        $template->set('treePath', $treePath);
        $template->set('news', $news);
        $template->set('action', '?action=editSave');
        $template->set('title', $news->getTitle());
        $template->set('isVisible', $news->getIsVisible());
        $template->set('preview', $news->getProperty('preview'));
        $template->set('file', $news->getProperty('file'));
        $template->fillAndPrint();
        return true;
    }

    public function handleSaveEdited(TreePath $treePath, $params = array()) {
        $news = $treePath->getNode();
        $news->setTitle((string)@$_POST['title']);
        $news->setIsVisible((bool)@$_POST['isVisible']);
        $news->setProperty('preview', (string)@$_POST['preview']);
        $news->setProperty('file', (string)@$_POST['file']);
        $result = Tree::persistNode($news);
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
