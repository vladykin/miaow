<?php

// $Id$

require_once('lib/Templates.php');

/**
 * HTTP support functions.
 */
class HTTP {

    function seeOther($url) {
        assert('is_string($url) && substr($url, 0, 1) == \'/\'');
        header('HTTP/1.1 303 See Other');
        header('Location: ' . $url);
        $template = new SkinTemplate('http/303');
        $template->set('url', $url);
        $template->fillAndPrint();
    }

    function forbidden() {
        header('HTTP/1.1 403 Forbidden');
        $template = new SkinTemplate('http/403');
        $template->fillAndPrint();
    }

    function notFound() {
        header('HTTP/1.1 404 Not Found');
        $template = new SkinTemplate('http/404');
        $template->fillAndPrint();
    }

    function internalServerError() {
        header('HTTP/1.1 500 Internal Server Error');
        $template = new SkinTemplate('http/500');
        $template->fillAndPrint();
    }

}

?>
