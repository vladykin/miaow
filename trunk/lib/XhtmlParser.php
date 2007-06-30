<?php

// $Id$

define('XHTML_HEADER', 
    '<?xml version="1.0" encoding="utf-8"?>' .
    '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"' .
    '    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' .
    '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">' .
    '<body>');

define('XHTML_FOOTER', 
    '</body></html>');

class XhtmlParser {

    private $transformer;

    private $stack;
    private $content;
    private $emptyTag;

    public function parse($xhtml, LinkTransformer $transformer) {
        assert('is_string($xhtml)');
        if (substr($xhtml, 0, 5) != '<?xml') {
            // dirty hack to handle old pages
            // all pages should eventually use strict XHTML syntax
            $xhtml = XHTML_HEADER . iconv('cp1251', 'utf8', $xhtml) . XHTML_FOOTER;
        }
        $this->stack = array();
        $this->content = '';
        $this->emptyTag = false;
        $this->transformer = $transformer;
        $parser = xml_parser_create('UTF-8');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, 'handleOpenTag', 'handleCloseTag');
        xml_set_character_data_handler($parser, 'handleCharData');
        xml_set_default_handler($parser, 'handleCharData');
        xml_parse($parser, $xhtml);
//        echo(xml_error_string(xml_get_error_code($parser)));
        xml_parser_free($parser);
    }

    public function parseFile($xhtmlfile, LinkTransformer $transformer) {
        assert('is_string($xhtmlfile) && file_exists($xhtmlfile)');
        $this->parse(file_get_contents($xhtmlfile), $transformer);
    }
    
    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    public function handleOpenTag($parser, $name, $attrs) {
//        echo("open($name)");
        $this->stack[] = $name;
        if (count($this->stack) > 2 && $this->stack[1] == 'body') {
            if ($name == 'a' && array_key_exists('href', $attrs)) {
                $attrs['href'] = $this->transformer->transform($attrs['href']);
            } elseif ($name == 'applet' && array_key_exists('archive', $attrs)) {
                $attrs['archive'] = $this->transformer->transform($attrs['archive']);
            } elseif ($name == 'img' && array_key_exists('src', $attrs)) {
                $attrs['src'] = $this->transformer->transform($attrs['src']);
            }
            $this->content .= "<$name";
            foreach ($attrs as $attr => $value) {
                $this->content .= " $attr=\""
                    . htmlspecialchars($value) . "\"";
            }
            if (!$this->isEmptyTag($name)) {
                $this->content .= '>';
            }
        }
    }

    public function handleCloseTag($parser, $name) {
//        echo("close($name)");
        if (count($this->stack) > 2 && $this->stack[1] == 'body') {
            if ($this->isEmptyTag($name)) {
                $this->content .= ' />';
            } else {
                $this->content .= "</$name>";
            }
        }
        array_pop($this->stack);
    }

    public function handleCharData($parser, $data) {
//        echo("char($data)");
        if (count($this->stack) >= 2 && $this->stack[1] == 'body') {
            if (!$this->isEmptyTag(end($this->stack))) {
                $this->content .= $data;
            }
        }
    }

    private function isEmptyTag($name) {
        return $name == 'br' || $name == 'hr' || $name == 'img' || $name == 'param';
    }

}

?>
