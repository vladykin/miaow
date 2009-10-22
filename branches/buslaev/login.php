<?php

// $Id$

require_once('config.php');

require_once('lib/HTTP.php');
require_once('lib/Session.php');
require_once('lib/Templates.php');

if (array_key_exists('password', $_POST)
        && Session::doLogin($_POST['password']))
{
    if (array_key_exists('redirect', $_GET)) {
        HTTP::seeOther($_GET['redirect']);
    } else {
        HTTP::seeOther(SITE_URL . '/admin.php');
    }    
} else {
    $template = new SkinTemplate('admin/login');
    $template->set('action',
        htmlspecialchars(SITE_URL . '/login.php?' . $_SERVER['QUERY_STRING']));
    $template->fillAndPrint();
}

?>
