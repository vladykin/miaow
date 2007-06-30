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


/**
 * Helper class with default implementations of some methods.
 */
/*
abstract class DefaultHandler {

    public function handleEdit(TreePath $treePath, $params = array()) {
        $treeNode = $treePath->getNode();
        $properties = $this->getProperties($treePath);
        if (empty($_POST)) {
            $template = new SkinTemplate('admin/node_edit', array(
                'action' => '?action=edit',
                'properties' => $properties
            ));
            $template->fillAndPrint();
        } else {
            foreach ($properties as &$property) {
                $result = $property->parseValue(@$_POST[$property->getName()]);
                //assert('$result === true');
                switch ($property->getName()) {
                case 'name':
                    $treeNode->setName($property->getValue());
                    break;
                case 'title':
                    $treeNode->setTitle($property->getValue());
                    break;
                case 'typeName':
                    $treeNode->setTypeName($property->getValue());
                    break;
                case 'hasOwnDir':
                    $treeNode->setHasOwnDir($property->getValue());
                    break;
                default:
                    $treeNode->setProperty($property->getName(), $property->getValue());
                }
            }
            assert('Tree::persistNode($treeNode) === true');
            HTTP::seeOther(SITE_URL . '/index.php/' . $treePath->toString());
        }
        return true;
    }

    public function handleNewChild(TreePath $treePath, $params = array()) {
        if (empty($_POST)) {
            $template = new SkinTemplate('admin/node_create', array(
                'action' => '?action=child',
                'nextpage' => 1,
                'properties' => array(
                    new TextProperty('Name', 'name', ''),
                    new OwnDirectoryProperty(true),
                    new SelectProperty('Type', 'typeName', 'Article', HandlerFactory::getKnownTypes())
                )
            ));
            $template->fillAndPrint();
        } elseif (@$_POST['page'] == 1) {
            $newNode = new TreeNode();
            $knownTypes = HandlerFactory::getKnownTypes();
            $newNode->setTypeName($knownTypes[$_POST['typeName']]);
            $newNode->setName($_POST['name']);
            $newNode->setHasOwnDir($_POST['hasOwnDir']);
            $treePath->pushNode($newNode);
            $handler = HandlerFactory::getHandler($newNode->getTypeName());
            $handler->setDefaults($treePath);
            $template = new SkinTemplate('admin/node_edit', array(
                'action' => '?action=child',
                'nextpage' => 2,
                'properties' => $handler->getProperties($treePath)
            ));
            $template->fillAndPrint();
        } else {
            $newNode = new TreeNode();
            $knownTypes = HandlerFactory::getKnownTypes();
            $newNode->setTypeName($knownTypes[$_POST['typeName']]);
            $newNode->setName($_POST['name']);
            $newNode->setHasOwnDir($_POST['hasOwnDir']);
            $treePath->pushNode($newNode);
            $handler = HandlerFactory::getHandler($newNode->getTypeName());
            $handler->setDefaults($treePath);
            $template = new SkinTemplate('admin/node_edit', array(
                'action' => '?action=child',
                'nextpage' => 2,
                'properties' => $handler->getProperties($treePath)
            ));
            $template->fillAndPrint();            
        }
        return true;
    }

}
*/

?>
