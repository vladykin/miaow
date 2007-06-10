<?php

// $Id$

/**
 * Encapsulates all TreeNode-type-dependent behaviour.
 */
interface Handler {

    /**
     * @param TreePath $treePath
     * @param array $options
     * @return boolean
     */
    public function handle(TreePath $treePath, $options);

    /**
     * @param TreePath $treePath
     * @param array $options
     * @return string
     */
    public function getPreview(TreePath $treePath, $options);

    /**
     * @param TreePath $treePath
     * @return array
     */
    public function getProperties(TreePath $treePath);

}

?>
