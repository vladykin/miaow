<?

// $Id$

require_once('lib/Entity.php');
require_once('lib/User.php');
require_once('lib/Keyword.php');
require_once('lib/Relation.php');
require_once('lib/Util.php');

class TreeNode extends Entity {

    function getTableName() {
        return TABLE_PREFIX . __CLASS__;
    }

    function getFields() {
        return array(
            new IntField('id', 'INT UNSIGNED NOT NULL'),
            new IntField('parentId', 'INT UNSIGNED NOT NULL'),
            new TextField('name', 'VARCHAR(128) NOT NULL'),
            new TextField('title', 'VARCHAR(128) NOT NULL'),
            new TextField('typeName', 'VARCHAR(16) NOT NULL'),
            new BoolField('hasOwnDir', 'BOOL NOT NULL'),
            new BoolField('isVisible', 'BOOL NOT NULL'),
            new Field('dateCreated', 'DATE NOT NULL'),
            new Field('datePublished', 'DATE NOT NULL'),
            new Field('dateModified', 'DATE NOT NULL'),
            new IntField('priority', 'INT NOT NULL'),
            new SerializedField('properties', 'BLOB')
        );
    }

    function getPrimaryKeyField() {
        return Entity::getField(__CLASS__, 'id');
    }

    /**
     * Numeric id of the page. Primary key.
     *
     * @access private
     * @var integer
     */
    var $id;

    /**
     * Numeric id of the parent page.
     *
     * @access private
     * @var integer
     */
    var $parentId;

    /**
     * Page name. Like file name.
     * Unique among each page's children.
     *
     * @access private
     * @var string
     */
    var $name;

    /**
     * Page title.
     *
     * @access private
     * @var string
     */
    var $title;

    /**
     * Page type.
     *
     * @access private
     * @var string
     */
    var $typeName;

    /**
     * True if node has separate directory to store its files.
     *
     * @access private
     * @var boolean
     */
    var $hasOwnDir;

    /**
     * Page visibility.
     *
     * @access private
     * @var boolean
     */
    var $isVisible;

    /**
     * Date when this entity was created.
     * Not to be confused with publication date.
     *
     * @access private
     * @var string
     */
    var $dateCreated;

    /**
     * Date of page publication.
     *
     * @access private
     * @var string
     */
    var $datePublished;

    /**
     * Date of page last modification.
     *
     * @access private
     * @var string
     */
    var $dateModified;

    /**
     * Page priority (used for sorting).
     *
     * @access private
     * @var integer
     */
    var $priority;

    /**
     * Custom properties.
     *
     * @access private
     * @var array
     */
    var $properties;

    /**
     * Cached authors.
     *
     * @access private
     * @var array
     */
    var $authors;
    
    /**
     * Cached provided keywords.
     *
     * @access private
     * @var array
     */
    var $providedKeywords;
    
    /**
     * Cached required keywords.
     *
     * @access private
     * @var array
     */
    var $requiredKeywords;

    /**
     * Default constructor.
     * Initializes some members with default values.
     *
     * @access public
     */
    function TreeNode() {
        $this->setId(0);
        $this->setParentId(0);
        $this->setDateCreated(date('Y-m-d'));
        $this->setDatePublished($this->getDateCreated());
        $this->setDateModified($this->getDateCreated());
        $this->setPriority(0);
        $this->properties = array();  
    }

    /**
     * @access public
     * @return integer
     */
    function getId() {
        assert('isset($this->id)');
        return $this->id;
    }

    /**
     * @access public
     * @param integer $id
     */
    function setId($id) {
        assert('Util::isValidId($id)');
        $this->id = intval($id);
    }

    /**
     * @access public
     * @return integer
     */
    function getParentId() {
        assert('isset($this->parentId)');
        return $this->parentId;
    }

    /**
     * @access public
     * @param integer $parentId
     */
    function setParentId($parentId) {
        assert('Util::isValidId($parentId)');
        $this->parentId = intval($parentId);
    }

    /**
     * @access public
     * @return string
     */
    function getName() {
        assert('isset($this->name)');
        return $this->name;
    }

    /**
     * @access public
     * @param string $name
     */
    function setName($name) {
        assert('Util::isValidName($name)');
        $this->name = $name;
    }

    /**
     * @access public
     * @return string
     */
    function getTitle() {
        assert('isset($this->title)');
        return $this->title;
    }

    /**
     * @access public
     * @param string $title
     */
    function setTitle($title) {
        assert('is_string($title) && strlen($title) > 0');
        $this->title = $title;
    }

    /**
     * @access public
     * @return integer
     */
    function getTypeName() {
        assert('isset($this->typeName)');
        return $this->typeName;
    }

    /**
     * @access public
     * @param integer $typeName
     */
    function setTypeName($typeName) {
        assert('is_string($typeName) && strlen($typeName) > 0');
        $this->typeName = $typeName;
    }

    /**
     * @access public
     * @return boolean
     */
    function getHasOwnDir() {
        assert('isset($this->hasOwnDir)');
        return $this->hasOwnDir;
    }

    /**
     * @access public
     * @param boolean $hasOwnDir
     */
    function setHasOwnDir($hasOwnDir) {
        assert('Util::isValidBoolean($hasOwnDir)');
        $this->hasOwnDir = (bool) $hasOwnDir;
    }

    /**
     * @access public
     * @return boolean
     */
    function getIsVisible() {
        assert('isset($this->isVisible)');
        return $this->isVisible;
    }

    /**
     * @access public
     * @param boolean $isVisible
     */
    function setIsVisible($isVisible) {
        assert('Util::isValidBoolean($isVisible)');
        $this->isVisible = (bool) $isVisible;
    }

    /**
     * @access public
     * @return string
     */
    function getDateCreated() {
        assert('isset($this->dateCreated)');
        return $this->dateCreated;
    }

    /**
     * @access public
     * @param string $dateCreated
     */
    function setDateCreated($dateCreated) {
        assert('Util::isValidDate($dateCreated)');
        $this->dateCreated = $dateCreated;
    }

    /**
     * @access public
     * @return string
     */
    function getDatePublished() {
        assert('isset($this->datePublished)');
        return $this->datePublished;
    }

    /**
     * @access public
     * @param string $datePublished
     */
    function setDatePublished($datePublished) {
        assert('Util::isValidDate($datePublished)');
        $this->datePublished = $datePublished;
    }

    /**
     * @access public
     * @return string
     */
    function getDateModified() {
        assert('isset($this->dateModified)');
        return $this->dateModified;
    }

    /**
     * @access public
     * @param string $dateModified
     */
    function setDateModified($dateModified) {
        assert('Util::isValidDate($dateModified)');
        $this->dateModified = $dateModified;
    }

    /**
     * @access public
     * @return integer
     */
    function getPriority() {
        assert('isset($this->priority)');
        return $this->priority;
    }

    /**
     * @access public
     * @param integer $priority
     */
    function setPriority($priority) {
        assert('Util::isValidInteger($priority)');
        $this->priority = intval($priority);
    }

    /**
     * @access public
     * @param string $name
     * @return mixed
     */
    function getProperty($name) {
        assert('is_string($name) && strlen($name) > 0');
        return @$this->properties[$name];
    }

    function setProperty($name, $value) {
        assert('is_string($name) && strlen($name) > 0');
        $this->properties[$name] = $value;
    }

    /**
     * @access public
     * @return array
     */
    function getProperties() {
        assert('isset($this->properties)');
        return $this->properties;
    }

    /**
     * @access public
     * @param array $properties
     */
    function setProperties($properties) {
        assert('is_array($properties)');
        $this->properties = $properties;
    }


// this is a caching front-end for RelationManager
    
    function getAuthors() {
        if (!isset($this->authors)) {
            $this->authors = RelationManager::getMapping('CreatedBy', $this);
        }
        return $this->authors;
    }

    function setAuthors($authors) {
        assert('Util::isArrayOf($authors, \'User\')');
        RelationManager::setMapping('CreatedBy', $this, $authors);
        $this->authors = $authors;
    }

    function getProvidedKeywords() {
        if (!isset($this->providedKeywords)) {
            $this->providedKeywords = RelationManager::getMapping('Provides', $this);
        }
        return $this->providedKeywords;
    }

    function setProvidedKeywords($keywords) {
        assert('Util::isArrayOf($keywords, \'Keyword\')');
        RelationManager::setMapping('Provides', $this, $keywords);
        $this->providedKeywords = $keywords;
    }

    function getRequiredKeywords() {
        if (!isset($this->requiredKeywords)) {
            $this->requiredKeywords = RelationManager::getMapping('Requires', $this);
        }
        return $this->requiredKeywords;
    }

    function setRequiredKeywords($keywords) {
        assert('Util::isArrayOf($keywords, \'Keyword\')');
        RelationManager::setMapping('Requires', $this, $keywords);
        $this->requiredKeywords = $keywords;
    }

}

?>
