<?php
class Art_Query_Pagination implements Iterator {
    protected $_configuration;
    protected $_start;
    protected $_select;
    protected $_index;
    protected $_maxResults;
    protected $_countResults;
    protected $_results;
    protected $_page;

    public function __construct(Art_Database_Select $select, $index, $maxResults, $reset=false, $args=array(), $class=false, $columnAliases=array()) {
        $this->_select = $select;
        $this->_index = $index;
        $this->_maxResults = $maxResults;
        $session = Art_Session::useNamespace('pagination');
        if (!isset($session->{$this->_index}))
            $session->{$this->_index} = array();
        $session = $session->{$this->_index};
        if (isset($_REQUEST[$this->_index]))
            $session->start = (int) $_REQUEST[$this->_index];
        if ($reset && isset($session->start))
            unset($session->start);
        $countQuery = Art_Database::getInstance()->query($this->_select->count('id'), 'select');
        $countQuery->rewind();
        $fetchRow = $countQuery->current();
        $this->_countResults = $fetchRow[Art_Adapter_Database_Descriptor_Abstract::FIELD_COUNTING_ALIAS];
        $this->_start = isset($session->start) && $session->start < $this->_countResults ? $session->start : 0;
        $this->_results = Art_Mapper::getInstance()->fetchResultsOfQuery(Art_Database::getInstance()->query($this->_select->limit($this->_maxResults, $this->_start)->count(false), 'select'), $class, true, $columnAliases);
    }
    
    public static function resetAll(){
        $session = Art_Session::useNamespace('pagination');
        $session->reset();
    }

    public function count() {
        return $this->_countResults;
    }

    public function reset() {
        if (isset($session->start))
            unset($session->start);
    }

    public function countPages() {
        return $this->_countResults == 0 ? 0 : ceil($this->_countResults / $this->_maxResults);
    }

    public function getPage() {
        return $this->_countResults == 0 ? 0 : floor($this->_start / $this->_maxResults);
    }

    public function getPages($max=10) {
        $actual = $this->getPage() + 1; //1
        $nb = $this->countPages(); // 23
        $half = ceil($max / 2); // 5
        $pages = array();
        for ($i = $actual - $half + 1; $i < ($actual + ($actual > $half ? $half + 1 : $max - $actual + 1)); $i++) {
            if ($i > 0)
                $pages[$i] = array('actual' => $i == $actual, 'text' => $i, 'index' => ($i - 1) * $this->_maxResults);
            if ($i == $nb)
                break;
        }
        return $pages;
    }

    public function hasNextPage() {
        return $this->getPage() < $this->countPages() - 1;
    }

    public function hasPreviousPage() {
        return $this->getPage() > 0;
    }

    public function getNextPage() {
        $actual = $this->getPage();
        return array('actual' => false, 'text' => $actual + 1, 'index' => ($actual + 1) * $this->_maxResults);
    }

    public function getPreviousPage() {
        $actual = $this->getPage();
        return array('actual' => false, 'text' => $actual - 1, 'index' => ($actual - 1) * $this->_maxResults);
    }

    public function getFirstPage() {
        return array('actual' => false, 'text' => '1', 'index' => 0);
    }

    public function getLastPage() {
        $count = $this->countPages();
        return array('actual' => false, 'text' => $count + 1, 'index' => ($count - 1) * $this->_maxResults);
    }

    public function getIndex() {
        return $this->_index;
    }

    public function rewind() {
        $this->_results->rewind();
    }

    public function current() {
        return $this->_results->current();
    }

    public function key() {
        return $this->_results->key();
    }

    public function next() {
        return $this->_results->next();
    }

    public function valid() {
        return $this->_results->valid();
    }
}
?>
