<?php

// $Id$

require_once('config.php');

require_once('lib/Cache.php');
require_once('lib/Keyword.php');
require_once('lib/TreeNode.php');
require_once('lib/User.php');

Cache::uninstall();
Cache::install();

EntityManager::uninstallEntityClass('TreeNode');
EntityManager::installEntityClass('TreeNode');

EntityManager::uninstallEntityClass('Keyword');
EntityManager::installEntityClass('Keyword');

EntityManager::uninstallEntityClass('User');
EntityManager::installEntityClass('User');

$root = new TreeNode();
$root->setName('root');
$root->setTitle('Root node');
$root->setTypeName('Article');
$root->setIsVisible(true);
$root->setHasOwnDir(true);
$root->setProperty('file', 'index.html');
EntityManager::persist($root);

RelationManager::uninstallRelationClass('Provides');
RelationManager::installRelationClass('Provides');

RelationManager::uninstallRelationClass('Requires');
RelationManager::installRelationClass('Requires');

$user = new User();
$user->setName('Guest');
$user->setEmail('guest@guest.com');
$user->setPassword('guest');
EntityManager::persist($user);

$user = new User();
$user->setName('Admin');
$user->setEmail('admin@admin.com');
$user->setPassword('admin');
EntityManager::persist($user);

RelationManager::uninstallRelationClass('CreatedBy');
RelationManager::installRelationClass('CreatedBy');

header('Content-type: text/plain');
echo("done\n");

?>
