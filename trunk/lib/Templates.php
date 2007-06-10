<?php

// $Id$

require_once('lib/Util.php');

/**
 * Templates are used to separate application logic from presentation.
 */
class Template {

    /**
     * @var string
     */
    private $file;

    /**
     * @var array
     */
    private $vars;

    /**
     * @param string $file
     * @param array $vars
     */
    public function __construct($file, $vars = array()) {
        assert('is_string($file) && is_file($file)');
        assert('is_null($vars) || is_array($vars)');
        $this->file = $file;
        $this->vars = $vars;
    }

    /**
     *
     */
    public function resetVars() {
        $this->vars = array();
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value) {
        assert('is_array($this->vars)');
        assert('is_string($name) && strlen($name) > 0');
        $this->vars[$name] = $value;
    }

    /**
     *
     */
    public function fillAndPrint() {
        foreach ($this->vars as $name => $value) {
            eval("\$$name = \$value;");
        }
        include($this->file);
    }

    /**
     * @return string
     */
    public function fillAndReturn() {
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
        parent::__construct(SITE_DIR . '/' . SKIN . '/layout/' . $layoutName . '.phtml', $vars);
    }

}


class ContentTemplate extends Template {

    function ContentTemplate($contentName, $vars = array()) {
        parent::__construct(SITE_DIR . '/' . SKIN . '/pages/' . $contentName . '.phtml', $vars);
    }

}


class PageTemplate extends LayoutTemplate {
    
    function PageTemplate($layoutName, $contentName, $vars = array()) {
        parent::__construct($layoutName, $vars);
        $this->set('content', new ContentTemplate($contentName, $vars));
    }

}


class TreeNodeTemplate extends Template {

    function TreeNodeTemplate($file, $vars = array()) {
        parent::__construct(SITE_DIR . '/' . SKIN . '/nodes/' . $file . '.phtml', $vars);
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
