<?php

// $Id$

class HandlerFactory {

    /**
     * @access public static
     * @return array
     */    
    function getKnownTypes() {
        return array('Text', 'List', 'News', 'Catalogue');
    }

    /**
     * @access public static
     * @param string $nodeTypeName
     * @return Handler
     */
    function &getHandler($nodeTypeName) {
        assert('is_string($nodeTypeName) && strlen($nodeTypeName) > 0');
        return HandlerFactory::createHandler($nodeTypeName . 'Handler');
    }

    /**
     * @access private static
     * @param string $className
     * @return Handler
     */
    function &createHandler($className) {
        assert('is_string($className) && strlen($className) > 0');
        static $instances = array();
        if (!array_key_exists($className, $instances)) {
            require_once(dirname(__FILE__) . "/handlers/$className.php");
            $instances[$className] =& new $className();
        }
        return $instances[$className];
    }

}

?>
