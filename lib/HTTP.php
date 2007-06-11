<?php

// $Id$

require_once(dirname(__FILE__) . '/Templates.php');

/**
 * HTTP support functions.
 */
class HTTP {

    function seeOther($url) {
        assert('is_string($url) && substr($url, 0, 1) == \'/\'');
        header('HTTP/1.1 303 See Other');
        header('Location: ' . $url);
        $content = new ContentTemplate('http_see_other');
        $layout = new LayoutTemplate('minimal');
        $layout->set('content', $content);
        $layout->fillAndPrint();
    }

    function forbidden() {
        header('HTTP/1.1 403 Forbidden');
        $content = new ContentTemplate('http_forbidden');
        $layout = new LayoutTemplate('minimal');
        $layout->set('content', $content);
        $layout->fillAndPrint();
    }

    function notFound() {
        header('HTTP/1.1 404 Not Found');
        $content = new ContentTemplate('http_not_found');
        $layout = new LayoutTemplate('minimal');
        $layout->set('content', $content);
        $layout->fillAndPrint();
    }

    function internalServerError() {
        header('HTTP/1.1 500 Internal Server Error');
        $content = new ContentTemplate('http_internal_error');
        $layout = new LayoutTemplate('minimal');
        $layout->set('content', $content);
        $layout->fillAndPrint();
    }

}

?>
