<?php

// $Id$

require_once(dirname(__FILE__) . '/Util.php');

/**
 * Templates are used to separate application logic from presentation.
 */
class Template {

    /**
     * @access private
     * @var string
     */
    var $file;

    /**
     * @access private
     * @var array
     */
    var $vars;

    /**
     * @access public
     * @param string $file
     * @param array $vars
     */
    function Template($file, $vars = array()) {
        assert('is_string($file) && is_file($file)');
        assert('is_null($vars) || is_array($vars)');
        $this->file = $file;
        $this->vars = $vars;
    }

    /**
     * @access public
     */
    function resetVars() {
        $this->vars = array();
    }

    /**
     * @access public
     * @param string $name
     * @param mixed $value
     */
    function set($name, $value) {
        assert('is_array($this->vars)');
        assert('is_string($name) && strlen($name) > 0');
        $this->vars[$name] =& $value;
    }

    /**
     * @access public
     */
    function fillAndPrint() {
        foreach ($this->vars as $name => $value) {
            eval("\$$name = \$value;");
        }
        include($this->file);
    }

    /**
     * @access public
     * @return string
     */
    function fillAndReturn() {
        foreach ($this->vars as $name => $value) {
            eval("\$$name = \$value;");
        }
        ob_start();
        include($this->file);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

}


class LayoutTemplate extends Template {

    function LayoutTemplate($layoutName, $vars = array()) {
        parent::Template(SITE_DIR . '/' . SKIN . '/layout/' . $layoutName . '.phtml', $vars);
    }

}


class ContentTemplate extends Template {

    function ContentTemplate($contentName, $vars = array()) {
        parent::Template(SITE_DIR . '/' . SKIN . '/pages/' . $contentName . '.phtml', $vars);
    }

}


class PageTemplate extends LayoutTemplate {
    
    function PageTemplate($layoutName, $contentName, $vars = array()) {
        parent::LayoutTemplate($layoutName);
        $this->set('content', new ContentTemplate($contentName, $vars));
    }

}


class TreeNodeTemplate extends Template {

    function TreeNodeTemplate($file, $vars = array()) {
        parent::Template(SITE_DIR . '/' . SKIN . '/nodes/' . $file . '.phtml', $vars);
    }

    function printAuthors($authors) {
        assert('Util::isArrayOf($authors, \'User\')');
//        echo(count($authors) == 1? 'Автор: ' : 'Авторы: ');
        $first = true;
        foreach ($authors as $author) {
            if ($first) {
                $first = false;
            } else {
                echo(', ');
            }
            echo($author->getName());
        }
    }

    function printKeywords($keywords) {
        assert('Util::isArrayOf($keywords, \'Keyword\')');
        $first = true;
        foreach ($keywords as $keyword) {
            if ($first) {
                $first = false;
            } else {
                echo(', ');
            }
            echo($keyword->getTitle());
        }
    }

}

?>
