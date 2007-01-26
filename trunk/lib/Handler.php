<?php

// $Id$

/**
 * Encapsulates all TreeNode-type-dependent behaviour.
 * This is abstract base class.
 */
class Handler {

    /**
     * @access public abstract
     * @param TreePath $treePath
     * @param User $user
     * @return boolean
     */
    function handle(&$treePath, &$user) {
        assert('false');
    }

    /**
     * @access public abstract
     * @param TreePath $treePath
     * @param User $user
     * @return string
     */
    function getPreview(&$treePath, &$user) {
        assert('false');
    }

    /**
     * @access public abstract
     * @param TreePath $treePath
     * @return array
     */
    function getProperties(&$treePath) {
        assert('false');
    }

}

?>