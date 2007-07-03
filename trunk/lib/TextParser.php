<?php

// $Id$

class TextParser {

    var $linkTransformer;

    function parse($text, LinkTransformer $linkTransformer) {
        assert('is_string($text)');
        $this->linkTransformer = $linkTransformer;
        $text = preg_replace_callback(
            '/(action|archive|href|src)\s*\=\s*\"(.*?)\"/',
            array($this, 'transformLink'),
            $text);
        $text = preg_replace('/(\r?\n){2,}/', "</p>\n<p>", $text);
        $text = '<p>' . $text . '</p>';
        return $text;
    }

    function transformLink($m) {
        return $m[1] . '="' . $this->linkTransformer->transform($m[2]) . '"';
    }

}

?>
