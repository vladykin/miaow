<?php

// $Id$

/**
 * Templates are used to separate application logic from presentation.
 */
abstract class Template {

    /**
     * @var array
     */
    protected $vars;

    /**
     * Constructs new template.
     */
    public function __construct($vars = array()) {
        assert('is_array($vars)');
        $this->vars = $vars;
    }

    /**
     * Resets all variables to null at once.
     */
    public function resetVars() {
        $this->vars = array();
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value) {
        assert('is_string($name) && strlen($name) > 0');
        $this->vars[$name] = $value;
    }

    /**
     * @return string
     */
    public function fillAndReturn() {
        ob_start();
        $this->fillAndPrint();
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
    
    /**
     *
     */
    public abstract function fillAndPrint();

}

class SkinTemplate extends Template {

    private $url;

    public function __construct($url, $vars = array()) {
        assert('is_string($url)');
        parent::__construct($vars);
        $this->url = $url;
    }

    public function fillAndPrint() {
        foreach ($this->vars as $name => $value) {
            eval("\$$name = \$value;");
        }
        include(SITE_DIR . '/' . SKIN . '/' . $this->url . '.phtml');
    }

}

?>
