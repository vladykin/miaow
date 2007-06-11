<?php

// $Id$

require_once('lib/Util.php');

abstract class Property {

    private $title;
    private $name;
    private $value;

    public function __construct($title, $name, $value) {
        assert('is_string($title) && strlen($title) > 0');
        assert('Util::isValidName($name)');
        $this->title = $title;
        $this->name = $name;
        $this->value = $value;
    }

    public function getTitle() {
        assert('isset($this->title)');
        return $this->title;
    }

    public function getName() {
        assert('isset($this->name)');
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * @return boolean
     */
    public abstract function parseValue($value);

    /**
     * @return string
     */
    public abstract function getControl();

}


class FileProperty extends Property {

    private $dir;

    public function __construct($title, $name, $value, $dir) {
        assert('is_string($dir) && is_dir($dir)');
        parent::__construct($title, $name, $value);
        $this->dir = $dir;
    }

    public function getControl() {
        $result = '<select id="' . $this->getName() . '"' . 
                  ' name="' . $this->getName() . "\">\n";
        $handle = opendir($this->dir);
        while (($file = readdir($handle)) !== false) {
            if (is_file("$this->dir/$file")) {
                $result .= '<option';
                if ($this->getValue() === $file) {
                    $result .= ' selected="selected"';
                }
                $result .= '>' .  htmlspecialchars($file) . "</option>\n";
            }
        }
        closedir($handle);
        $result .= "</select>\n";
        return $result;
    }

    function parseValue($value) {
        if (is_string($value) && is_file("$this->dir/$value")) {
            $this->setValue($value);
            return true;
        } else {
            return false;
        }
    }

}


class KeywordListProperty extends Property {

    public function __construct($title, $name, $value) {
        parent::__construct($title, $name, $value);
    }

    public function setValue($value) {
        assert('is_array($value)');
        parent::setValue($value);
    }
    
    public function getControl() {
        return '<input type="text" id="' . $this->getName() . '"' .
               ' name="' . $this->getName() . '"' . 
               ' value="' . implode($this->getValue()) . "\" />\n";
    }

    public function parseValue($value) {
        assert('is_string($value)');
        $this->setValue(explode(',', $value));
        return true;
    }

}


class OrderProperty extends SelectProperty {

    public function __construct($title, $name, $value) {
        assert('is_null($value) || is_string($value)');
        parent::__construct($title, $name, $value, array(
            'title ASC' => 'By title, ascending',
            'datePublished DESC' => 'By publishing date, descending'
        ));
    }

}


class OwnDirectoryProperty extends SelectProperty {

    public function __construct($value) {
        assert('is_bool($value)');
        parent::__construct(
            'Directory', 
            'hasOwnDir', 
            (int) $value,
            array(
                'Separate subdirectory',
                'Parent node directory'
            )
        );
    }

}


class SelectProperty extends Property {

    private $values;

    public function __construct($title, $name, $value, $values) {
        assert('is_null($value) || is_integer($value) || is_string($value)');
        assert('is_array($values)');
        parent::__construct($title, $name, $value);
        $this->values = $values;
    }

    public function getControl() {
        $result = '<select id="' . $this->getName() . '"' .
                  ' name="' . $this->getName() . "\">\n";
        foreach ($this->values as $key => $value) {
            $result .= '<option value="' . htmlspecialchars($key) . '"';
            if ($this->getValue() === $key) {
                $result .= ' selected="selected"';
            }
            $result .= '>' .  htmlspecialchars($value) . "</option>\n";
        }
        $result .= "</select>\n";
        return $result;
    }

    public function parseValue($value) {
        if (array_key_exists($value, $this->values)) {
            $this->setValue($value);
            return true;
        } else {
            return false;
        }
    }

}


class TextareaProperty extends Property {

    public function __construct($title, $name, $value) {
        parent::__construct($title, $name, $value);
    }

    public function getControl() {
        return '<textarea id="' . $this->getName() . '"' .
           ' name="' . $this->getName() . '" rows="20" cols="48">' .
               htmlspecialchars($this->getValue()) .
           "</textarea>\n";
    }

    public function parseValue($value) {
        if (is_string($value)) {
            $this->setValue($value);
            return true;
        } else {
            return false;
        }
    }

}


class TextProperty extends Property {

    public function __construct($title, $name, $value) {
        assert('is_string($value)');
        parent::__construct($title, $name, $value);
    }

    public function getControl() {
        return '<input type="text" id="' . $this->getName() . '"' . 
               ' name="' . $this->getName() . '"' .
               ' value="' . htmlspecialchars($this->getValue()) . '"' .
               " size=\"48\" />\n";
    }

    public function parseValue($value) {
        if (is_string($value)) {
            $this->setValue($value);
            return true;
        } else {
            return false;
        }
    }

}


class UserListProperty extends Property {

    public function __construct($title, $name, $value) {
        parent::__construct($title, $name, $value);
    }

    public function setValue($value) {
        assert('is_array($value)');
        parent::setValue($value);
    }
    
    public function getControl() {
        return '<input type="text" id="' . $this->getName() . '"' .
               ' name="' . $this->getName() . '"' . 
               ' value="' . implode($this->getValue()) . "\" />\n";
    }

    public function parseValue($value) {
        assert('is_string($value)');
        $this->setValue(explode(',', $value));
        return true;
    }

}


class VisibilityProperty extends SelectProperty {

    public function __construct($value) {
        assert('is_bool($value)');
        parent::__construct(
            'Visibility', 
            'isVisible', 
            (int) $value,
            array(
                'Visible for Administrator Only',
                'Visible for Any User'
            )
        );
    }

}

?>
