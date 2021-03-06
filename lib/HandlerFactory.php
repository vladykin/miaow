<?php

// $Id$

class HandlerFactory {

    /**
     * This is utility class that can't be instantiated.
     */
    private function __construct() {}

    /**
     * @return array
     */    
    public static function getKnownTypes() {
        static $knownTypes = array();
        if (empty($knownTypes)) {
            $dh = opendir(dirname(__FILE__) . '/handlers/');
            while (($fn = readdir($dh)) !== false) {
                if (substr($fn, -4) === '.php') {
                    // trim 'Handler.php'
                    $knownTypes[] = substr($fn, 0, -11);
                }
            }
            closedir($dh);
        }
        return $knownTypes;
    }

    /**
     * @param string $nodeTypeName
     * @return Handler
     */
    public static function getHandler($nodeTypeName) {
        assert('is_string($nodeTypeName) && strlen($nodeTypeName) > 0');
        return HandlerFactory::createHandler($nodeTypeName . 'Handler');
    }

    /**
     * @param string $className
     * @return Handler
     */
    private static function createHandler($className) {
        assert('is_string($className) && strlen($className) > 0');
        static $instances = array();
        if (!array_key_exists($className, $instances)) {
            require_once(dirname(__FILE__) . "/handlers/$className.php");
            $instances[$className] = new $className();
        }
        return $instances[$className];
    }

}

?>
