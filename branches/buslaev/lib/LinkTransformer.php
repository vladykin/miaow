<?php

// $Id$

class LinkTransformer {

    var $basePageUrl;
    var $baseFileUrl;
    var $spoilEmail;

    function LinkTransformer($basePageUrl, $baseFileUrl, $spoilEmail = false) {
        assert('is_string($basePageUrl)');
        assert('is_string($baseFileUrl)');
        assert('is_bool($spoilEmail)');
        $this->basePageUrl = $basePageUrl;
        $this->baseFileUrl = $baseFileUrl;
        $this->spoilEmail = $spoilEmail;
    }

    function transform($link) {
        assert('is_string($link)');
        if (strncmp($link, '#', 1) == 0
                || strncmp($link, 'ftp:', 4) == 0
                || strncmp($link, 'http:', 5) == 0
        ) {
            return $link;
        }
        if (strncmp($link, 'mailto:', 7) == 0 && $this->spoilEmail) {
            return preg_replace(array('/\@/', '/\./'),
                                array(' at ', ' dot '), $link);
        }
        if (strncmp($link, 'view:', 5) == 0) {
            // hack to handle old pages correctly
            // to be removed (eventually)
            $link = substr($link, 5);
        }
        if (strncmp($link, '/', 1) == 0) {
            return $this->basePageUrl . $link;
        }
        if ($link{strlen($link) - 1} != '/') {
            return $this->baseFileUrl . '/' . $link;
        }
        return $link; // don't know what to do
    }

}

?>
