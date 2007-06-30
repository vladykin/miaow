<?

// $Id$

require_once('lib/Entity.php');
require_once('lib/User.php');
require_once('lib/Keyword.php');
require_once('lib/Relation.php');
require_once('lib/Util.php');

class TreeNode extends Entity {

    public static function getTableName() {
        return TABLE_PREFIX . __CLASS__;
    }

    public static function getFields() {
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

    public static function getPrimaryKeyField() {
        return Entity::getField(__CLASS__, 'id');
    }

    /**
     * Numeric id of the page. Primary key.
     *
     * @var integer
     */
    private $id;

    /**
     * Numeric id of the parent page.
     *
     * @var integer
     */
    private $parentId;

    /**
     * Page name. Like file name.
     * Unique among each page's children.
     *
     * @var string
     */
    private $name;

    /**
     * Page title.
     *
     * @var string
     */
    private $title;

    /**
     * Page type.
     *
     * @var string
     */
    private $typeName;

    /**
     * True if node has separate directory to store its files.
     *
     * @var boolean
     */
    private $hasOwnDir;

    /**
     * Page visibility.
     *
     * @var boolean
     */
    private $isVisible;

    /**
     * Date when this entity was created.
     * Not to be confused with publication date.
     *
     * @var string
     */
    private $dateCreated;

    /**
     * Date of page publication.
     *
     * @var string
     */
    private $datePublished;

    /**
     * Date of page last modification.
     *
     * @var string
     */
    private $dateModified;

    /**
     * Page priority (used for sorting).
     *
     * @var integer
     */
    private $priority;

    /**
     * Custom properties.
     *
     * @var array
     */
    private $properties;

    /**
     * Cached authors.
     *
     * @var array
     */
    private $authors;
    
    /**
     * Cached provided keywords.
     *
     * @var array
     */
    private $providedKeywords;
    
    /**
     * Cached required keywords.
     *
     * @var array
     */
    private $requiredKeywords;

    /**
     * Default constructor.
     * Initializes some members with default values.
     */
    public function __construct() {
        $this->setId(0);
        $this->setParentId(0);
        $this->setDateCreated(date('Y-m-d'));
        $this->setDatePublished($this->getDateCreated());
        $this->setDateModified($this->getDateCreated());
        $this->setPriority(0);
        $this->properties = array();  
    }

    /**
     * @return integer
     */
    public function getId() {
        assert('isset($this->id)');
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id) {
        assert('Util::isValidId($id)');
        $this->id = intval($id);
    }

    /**
     * @return integer
     */
    public function getParentId() {
        assert('isset($this->parentId)');
        return $this->parentId;
    }

    /**
     * @param integer $parentId
     */
    public function setParentId($parentId) {
        assert('Util::isValidId($parentId)');
        $this->parentId = intval($parentId);
    }

    /**
     * @return string
     */
    public function getName() {
        assert('isset($this->name)');
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        assert('Util::isValidName($name)');
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getTitle() {
        assert('isset($this->title)');
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        assert('is_string($title) && strlen($title) > 0');
        $this->title = $title;
    }

    /**
     * @return integer
     */
    public function getTypeName() {
        assert('isset($this->typeName)');
        return $this->typeName;
    }

    /**
     * @param integer $typeName
     */
    public function setTypeName($typeName) {
        assert('is_string($typeName) && strlen($typeName) > 0');
        $this->typeName = $typeName;
    }

    /**
     * @return boolean
     */
    public function getHasOwnDir() {
        assert('isset($this->hasOwnDir)');
        return $this->hasOwnDir;
    }

    /**
     * @param boolean $hasOwnDir
     */
    public function setHasOwnDir($hasOwnDir) {
        assert('Util::isValidBoolean($hasOwnDir)');
        $this->hasOwnDir = (bool) $hasOwnDir;
    }

    /**
     * @return boolean
     */
    public function getIsVisible() {
        assert('isset($this->isVisible)');
        return $this->isVisible;
    }

    /**
     * @param boolean $isVisible
     */
    public function setIsVisible($isVisible) {
        assert('Util::isValidBoolean($isVisible)');
        $this->isVisible = (bool) $isVisible;
    }

    /**
     * @return string
     */
    public function getDateCreated() {
        assert('isset($this->dateCreated)');
        return $this->dateCreated;
    }

    /**
     * @param string $dateCreated
     */
    public function setDateCreated($dateCreated) {
        assert('Util::isValidDate($dateCreated)');
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return string
     */
    public function getDatePublished() {
        assert('isset($this->datePublished)');
        return $this->datePublished;
    }

    /**
     * @param string $datePublished
     */
    public function setDatePublished($datePublished) {
        assert('Util::isValidDate($datePublished)');
        $this->datePublished = $datePublished;
    }

    /**
     * @return string
     */
    public function getDateModified() {
        assert('isset($this->dateModified)');
        return $this->dateModified;
    }

    /**
     * @param string $dateModified
     */
    public function setDateModified($dateModified) {
        assert('Util::isValidDate($dateModified)');
        $this->dateModified = $dateModified;
    }

    /**
     * @return integer
     */
    public function getPriority() {
        assert('isset($this->priority)');
        return $this->priority;
    }

    /**
     * @param integer $priority
     */
    public function setPriority($priority) {
        assert('Util::isValidInteger($priority)');
        $this->priority = intval($priority);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getProperty($name) {
        assert('is_string($name) && strlen($name) > 0');
        return @$this->properties[$name];
    }

    public function setProperty($name, $value) {
        assert('is_string($name) && strlen($name) > 0');
        $this->properties[$name] = $value;
    }

    /**
     * @return array
     */
    public function getProperties() {
        assert('isset($this->properties)');
        return $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties($properties) {
        assert('is_array($properties)');
        $this->properties = $properties;
    }


// this is a caching front-end for RelationManager
    
    public function getAuthors() {
        if (!isset($this->authors)) {
            $this->authors = RelationManager::getMapping('CreatedBy', $this);
        }
        return $this->authors;
    }

    public function setAuthors($authors) {
        assert('Util::isArrayOf($authors, \'User\')');
        RelationManager::setMapping('CreatedBy', $this, $authors);
        $this->authors = $authors;
    }

    public function getProvidedKeywords() {
        if (!isset($this->providedKeywords)) {
            $this->providedKeywords = RelationManager::getMapping('Provides', $this);
        }
        return $this->providedKeywords;
    }

    public function setProvidedKeywords($keywords) {
        assert('Util::isArrayOf($keywords, \'Keyword\')');
        RelationManager::setMapping('Provides', $this, $keywords);
        $this->providedKeywords = $keywords;
    }

    public function getRequiredKeywords() {
        if (!isset($this->requiredKeywords)) {
            $this->requiredKeywords = RelationManager::getMapping('Requires', $this);
        }
        return $this->requiredKeywords;
    }

    public function setRequiredKeywords($keywords) {
        assert('Util::isArrayOf($keywords, \'Keyword\')');
        RelationManager::setMapping('Requires', $this, $keywords);
        $this->requiredKeywords = $keywords;
    }

}

?>
