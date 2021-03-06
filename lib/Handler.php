<?php

// $Id$

/**
 * Encapsulates all TreeNode-type-dependent behaviour.
 */
abstract class Handler {

    /**
     * Called to display the node in normal mode.
     *
     * @param TreePath $treePath
     * @param array $params
     * @return boolean
     */
    public abstract function handle(TreePath $treePath, $params = array());

    /**
     * Called to edit the node.
     * 
     * @param TreePath $treePath
     * @param array $params
     * @return boolean
     */
    public abstract function handleEdit(TreePath $treePath, $params = array());

    /**
     * Called to save edits.
     * 
     * @param TreePath $treePath
     * @param array $params
     * @return boolean
     */
    public abstract function handleSaveEdited(TreePath $treePath, $params = array());

    /**
     * Called to create a new node at the top of $treePath.
     * Added 'x' at the beginning of the method name to prevent index.php
     * from calling this method directly.
     *
     * @param TreePath $treePath
     * @param array $params
     * @return boolean
     */
    public abstract function xhandleCreate(TreePath $treePath, $params = array());

    /**
     * Called to save the newly created node at the top of $treePath.
     * Added 'x' at the beginning of the method name to prevent index.php
     * from calling this method directly.
     *
     * @param TreePath $treePath
     * @param array $params
     * @return boolean
     */
    public abstract function xhandleSaveCreated(TreePath $treePath, $params = array());

    /**
     * Called to create a new child of this node.
     * Passes control to appropriate handler's xhandleCreate().
     *
     * @param TreePath $treePath
     * @param array $params
     * @return boolean
     */
    public function handleCreate(TreePath $treePath, $params = array()) {
        $typeName = strval(@$_POST['typeName']);
        if ($typeName && in_array($typeName, HandlerFactory::getKnownTypes())) {
            $handler = HandlerFactory::getHandler($typeName);
            return $handler->xhandleCreate($treePath, $params);
        } else {
            $template = new SkinTemplate('admin/choose_type');
            $template->set('action', '?action=create');
            $template->fillAndPrint();
            return true;
        }
    }

    public function handleSaveCreated(TreePath $treePath, $params = array()) {
        $typeName = strval(@$_POST['typeName']);
        if ($typeName && in_array($typeName, HandlerFactory::getKnownTypes())) {
            $handler = HandlerFactory::getHandler($typeName);
            return $handler->xhandleSaveCreated($treePath, $params);
        } else {
            return false;
        }
    }

    public function handleDelete(TreePath $treePath, $params = array()) {
        $treeNode = $treePath->getNode();
        if (@$_POST['confirm'] == 'yes') {
            $result = Tree::removeNode($treeNode, true);
            $treePath->popNode();
            HTTP::seeOther($treePath->toURL());
        } else {
            $template = new SkinTemplate('admin/delete');
            $template->set('treePath', $treePath);
            $template->set('treeNode', $treeNode);
            $template->fillAndPrint();
            return true;
        }
    }

    /**
     * Called to save the newly created child of this node.
     * Passes control to appropriate handler's xhandleSaveCreated().
     *
     * @param TreePath $treePath
     * @param array $params
     * @return string
     */
    public function getPreview(TreePath $treePath, $params = array()) {
        $treeNode = $treePath->getNode();
        return '<a href="' . $treePath->toURL() . '">' . $treeNode->getTitle() . '</a>';
    }

}

?>
