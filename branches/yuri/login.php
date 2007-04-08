<?php

// $Id$

require_once('config.php');

require_once('lib/HTTP.php');
require_once('lib/Users.php');
require_once('lib/Templates.php');

session_start();

$user = Users::getCurrentUser();
assert('$user instanceof User');

if (array_key_exists('password', $_POST)) {
    $user = Users::doLogin($user, $_POST['password']);
    assert('is_a($user, \'User\')');
}

if ($user->isAdmin()) {
    if (array_key_exists('redirect', $_GET)) {
        HTTP::seeOther($_GET['redirect']);
    } else {
        HTTP::seeOther(SITE_URL . '/admin.php');
    }
} else {
    $template = new LayoutTemplate('admin');
    $template->set('title', 'Login form');
    $template->set('content', new ContentTemplate('login_form', array(
        'action' => htmlspecialchars(SITE_URL . '/login.php?' . $_SERVER['QUERY_STRING'])
    )));
    $template->fillAndPrint();
}

?>
