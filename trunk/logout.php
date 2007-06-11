<?php

// $Id$

require_once('config.php');

require_once('lib/HTTP.php');
require_once('lib/Session.php');

Session::doLogout();
if (array_key_exists('redirect', $_GET)) {
    HTTP::seeOther($_GET['redirect']);
} else {
    HTTP::seeOther(SITE_URL);
}    

?>
