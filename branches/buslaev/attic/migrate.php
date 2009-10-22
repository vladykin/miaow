<?php

// $Id:$

// Converts original CAT database into Miaow database

require_once('../config.php');

require_once(LIB_DIR . '/HandlerFactory.php');

require_once(LIB_DIR . '/TreeNode.php');
require_once(LIB_DIR . '/Keyword.php');
require_once(LIB_DIR . '/User.php');

require_once(LIB_DIR . '/Tree.php');
require_once(LIB_DIR . '/Storage.php');

$db =& Storage::getConnection();

$treePath =& new TreePath();
migrate(0, $treePath);

echo("done\n");

function migrate($parentId, &$treePath) {
    global $db;
    $query = 'SELECT * FROM ! WHERE parent_id = ?';
    $res =& $db->getAll($query, array('cat_records', $parentId), DB_FETCHMODE_ASSOC);
    assert('!DB::isError($res)');
    foreach ($res as $row) {
        $treeNode =& new TreeNode();
        $treeNode->setName(getName($row, $treePath));
        $treeNode->setTitle(getTitle($row, $treePath));
        $treeNode->setTypeName(getTypeName($row, $treePath));
        $treeNode->setVisible(getVisible($row, $treePath));
        $treeNode->setOwnDirectory(getOwnDirectory($row, $treePath));
        $treeNode->setDateCreated(getDateCreated($row, $treePath));
        $treeNode->setDatePublished(getDatePublished($row, $treePath));
        $treeNode->setDateModified(getDateModified($row, $treePath));
        $treeNode->setPriority(getPriority($row, $treePath));
        setProperties($treeNode, $row, $treePath);
        $res =& Tree::persistNode($treeNode, $treePath);
        assert('$res === true');
        setAuthors($treeNode, $row, $treePath);
        $treePath->pushNode($treeNode);
        echo($treePath->toString() . "\n");
        migrate($row['id'], $treePath);
        $treePath->popNode();
    }
}

function getName(&$row, &$treePath) {
    $name = $row['name'];
    return strlen($name) > 0? $name : 'root';
}

function getTitle(&$row, &$treePath) {
    $title = TextField::decode($row['caption']);
    $title = preg_replace('/ \(\d{4}\)$/', '', $title);
    return $title;
}

function getTypeName(&$row, &$treePath) {
    if ($treePath->getNodeCount() == 0) {
        return 'Article';
    }
    if ($treePath->getNodeCount() == 1) {
        if ($row['name'] == 'vis' || $row['name'] == 'theory') {
            return 'Catalogue';
        }
    }
    if ($treePath->getNodeCount() == 2) {
        $node =& $treePath->getNode(1);
        if ($node->getName() == 'news') {
            return 'News';
        }
        if ($node->getName() == 'links') {
            return 'Link';
        }
        if ($node->getName() == 'books') {
            return 'Book';
        }
    }
    if ($treePath->getNodeCount() == 3) {
        $node =& $treePath->getNode(1);
        if ($node->getName() == 'vis' || $node->getName() == 'theory') {
            return 'Article';
        }
    }
    if ($treePath->getNodeCount() > 2) {
        $node =& $treePath->getNode();
        if (strncmp($node->getName(), 'photos', 6) == 0 || $node->getName() == 'tyoma') {
            return 'Image';
        }
        if (preg_match('/\.(pdf|rar|zip|jpg|djvu|ppt)$/', $row['content'])) {
            return 'File';
        }
    }
    return 'List';
}

function getVisible(&$row, &$treePath) {
    return !($row['flags'] & 1);
}

function getOwnDirectory(&$row, &$treePath) {
    if ($treePath->getNodeCount() > 2) {
        $node =& $treePath->getNode(2);
        if ($node->getName() == 'news' || $node->getName() == 'links') {
            return false;
        }
    }
    if (preg_match('/\.(pdf|rar|zip|jpg|djvu|ppt)$/', $row['content'])) {
        return false;
    }
    return true;
}

function getDateCreated(&$row, &$treePath) {
    $title = TextField::decode($row['caption']);
    if (preg_match('/ \((\d{4})\)$/', $title, $m)) {
        return $m[1] . '-01-01';
    } else {
        return substr($row['time_created'], 0, 10);
    }
}

function getDatePublished(&$row, &$treePath) {
    return substr($row['time_created'], 0, 10);
}

function getDateModified(&$row, &$treePath) {
    return substr($row['time_modified'], 0, 10);
}

function getPriority(&$row, &$treePath) {
    return $row['priority'];
}

function setProperties(&$treeNode, &$row, &$treePath) {
    if ($treeNode->getTypeName() == 'List') {
        $treeNode->setProperty('order', 'title ASC');
        return;
    }
    if ($treeNode->getTypeName() == 'News') {
        $treeNode->setProperty('preview', simplify(TextField::decode($row['content'])));
        return;
    }
    if ($treeNode->getTypeName() == 'Link') {
        $treeNode->setProperty('preview', simplify(TextField::decode($row['content'])));
        $treeNode->setProperty('link', $row['data']);
        return;
    }
    if ($treeNode->getTypeName() == 'Article') {
        $treeNode->setProperty('listChildren', $treePath->getNodeCount() > 0);
        if (substr($row['content'], 0, 5) == 'file:') {
            $treeNode->setProperty('file', basename(substr($row['content'], 5)));
        }
        return;
    }
    if ($treeNode->getTypeName() == 'Book') {
        $treeNode->setProperty('authors', TextField::decode($row['data']));
        if (substr($row['content'], 0, 5) == 'file:') {
            $treeNode->setProperty('file', basename(substr($row['content'], 5)));
        }
        return;
    }
    if ($treeNode->getTypeName() == 'Image') {
        $treeNode->setProperty('preview', $row['data']);
        $treeNode->setProperty('file', basename(substr($row['content'], 5)));
        return;
    }
    if ($treeNode->getTypeName() == 'File') {
        $treeNode->setProperty('file', basename(substr($row['content'], 5)));
    }
}

function setAuthors(&$treeNode, &$row, &$treePath) {
    static $authorCache = array();
    if ($treePath->getNodeCount() == 3) {
        $sitearea =& $treePath->getNode(1);
        if ($sitearea->getName() == 'vis' || $sitearea->getName() == 'theory') {
            $data = TextField::decode($row['data']);
            if (strlen($data) == 0) return;
            $authors = array();
            foreach (explode(', ', $data) as $author) {
                if ($authorCache[$author]) {
                    $authors[] = $authorCache[$author];
                } else {
                    $user =& new User();
                    $user->setName($author);
                    if ($author == 'Владыкин А.') {
                        $user->setEmail('vladykin@rain.ifmo.ru');
                        $user->setPassword('qwerty');
                    }
                    EntityManager::persist($user);
                    $authors[] = $user;
                    $authorCache[$user->getName()] = $user;
                }
            }
            $treeNode->setAuthors($authors);
        }
    }
}

function simplify($text) {
    assert('is_string($text)');
    $text = preg_replace('/"view:([^"]+?)\/?"/', '"\1/"', $text);
    $text = preg_replace('/\n+/', '', $text);
    $text = preg_replace('/<p[^>]*?>/', '', $text);
    $text = preg_replace('/<\/p>/', "\n\n", $text);
    $text = preg_replace('/<br\s*\/>/', "\n", $text);
    return trim($text);
}

?>
