<?php

// $Id$

/**
 * Encapsulates all TreeNode-type-dependent behaviour.
 */
interface Handler {

    /**
     * Called to display the node in normal mode.
     *
     * @param TreePath $treePath
     * @param array $options
     * @return boolean
     */
    public function handle(TreePath $treePath, $options = array());

    /**
     * Called to edit the node.
     * 
     * @param TreePath $treePath
     * @param array $options
     * @return boolean
     */
    public function handleEdit(TreePath $treePath, $options = array());

    /**
     * Called to create a new child of the node.
     *
     * @param TreePath $treePath
     * @param array $options
     * @return boolean
     */
    public function handleChild(TreePath $treePath, $options = array());

    /**
     * Sets default property values for given node
     * (at top of $treePath).
     *
     * @param TreePath $treePath
     * @param array $options
     * @return boolean
     */
    public function setDefaults(TreePath $treePath, $options = array());

    /**
     * @param TreePath $treePath
     * @param array $options
     * @return string
     */
    public function getPreview(TreePath $treePath, $options = array());

    /**
     * @param TreePath $treePath
     * @return array
     */
    public function getProperties(TreePath $treePath);

}


/**
 * Helper class with default implementations of some methods.
 */
abstract class DefaultHandler {

    public function handleEdit(TreePath $treePath, $options = array()) {
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

    public function handleChild(TreePath $treePath, $options = array()) {
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

    public function setDefaults(TreePath $treePath, $options = array()) {
        $treeNode = $treePath->getNode();
        $treeNode->setTitle('Enter node title');
        $treeNode->setIsVisible(true);
    }

}

?>
