<?php

/**
 * $Id$
 */

require_once('lib/Handler.php');
require_once('lib/LinkTransformer.php');
require_once('lib/TextParser.php');
require_once('lib/Properties.php');
require_once('lib/Templates.php');


class NewsHandler implements Handler {

    /**
     * @access public
     * @param TreePath $treePath
     * @return boolean
     */
    public function handle(TreePath $treePath, $options = array()) {
        $treeNode = $treePath->getNode();
        $directory = $treePath->getDirectory();
        $file = $treeNode->getProperty('file');
        $transformer = new LinkTransformer(TREE_ROOT, '?');
        if (isset($file))  {
            $parser = new XhtmlParser();
            $parser->parseFile($file, $transformer);
            $content = $parser->getContent();
        } else {
            $parser = new TextParser();
            $content = $parser->parse($treeNode->getProperty('preview'), $transformer);
        }
        $t = new SkinTemplate('news/main');
        $t->set('title', $treeNode->getTitle());
        $t->set('whenPublished', $treeNode->getDatePublished());
        $t->set('content', $content);
        $t->set('authors', $treeNode->getAuthors());
        $template = new LayoutTemplate('default');
        $template->set('treePath', $treePath);
        $template->set('treeNode', $treeNode);
        $template->set('content', $t);
        $template->fillAndPrint();
        return true;
    }
    
    /**
     * @access public
     * @param TreePath $treePath
     * @return string
     */
    public function getPreview(TreePath $treePath, $options = array()) {
        $treeNode = $treePath->getNode();
        $parser = new TextParser();
        $transformer = new LinkTransformer(SITE_URL . '/index.php', '?');
        $t = new SkinTemplate('news/preview');
        $t->set('treePath', $treePath);
        $t->set('content', $parser->parse($treeNode->getProperty('preview'), $transformer));
        return $t->fillAndReturn();
    }

    /**
     * @access public
     * @param TreePath $treePath
     * @return array
     */
    public function getProperties(TreePath $treePath) {
        $treeNode = $treePath->getNode();
        return array(
            new TextProperty('Title', 'title', $treeNode->getTitle()),
            new TextareaProperty('Preview', 'preview', $treeNode->getProperty('preview')),
            new FileProperty('File', 'file',
                $treeNode->getProperty('file'),
                $treePath->getDirectory()),
            new VisibilityProperty($treeNode->isVisible()),
            new UserListProperty('Authors', 'authors', array()),
            new KeywordListProperty('Keywords', 'keywords', array()),
        );
    }

}

?>
