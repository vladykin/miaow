<?php

// $Id$

require_once('lib/Handler.php');
require_once('lib/LinkTransformer.php');
require_once('lib/XhtmlParser.php');
require_once('lib/Properties.php');
require_once('lib/Templates.php');

class ArticleHandler extends DefaultHandler {

    /**
     * @access public
     * @param TreePath $treePath
     * @param array $options
     * @return boolean
     */
    public function handle(TreePath $treePath, $options = array()) {
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

    /**
     * @access public abstract
     * @param TreePath $treePath
     * @param array $options
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
        $treeNode = $treePath->getNode();
        return array(
            new TextProperty('Title', 'title', $treeNode->getTitle()),
            new FileProperty('File', 'file',
                    $treeNode->getProperty('file'),
                    $treePath->getDirectory()),
            new VisibilityProperty($treeNode->getIsVisible())
        );
    }

}

?>
