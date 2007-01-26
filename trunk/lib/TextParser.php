<?php

// $Id$

class TextParser {

    var $linkTransformer;

    function parse($text, $linkTransformer) {
        assert('is_string($text)');
        assert('is_a($linkTransformer, \'LinkTransformer\')');
        $this->linkTransformer = $linkTransformer;
        $text = preg_replace_callback(
            '/(action|archive|href|src)\s*\=\s*\"(.*?)\"/',
            array($this, 'transformLink'),
            $text);
        $text = preg_replace('/\n{2,}/', "</p>\n<p>", $text);
        $text = '<p>' . $text . '</p>';
        return $text;
    }

    function transformLink($m) {
        return $m[1] . '="' . $this->linkTransformer->transform($m[2]) . '"';
    }

}

?>
