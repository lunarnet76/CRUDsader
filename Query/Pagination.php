<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Query {
	/**
	 * @category   Query
	 * @package    CRUDsader
	 */
	class Pagination extends \CRUDsader\Query {
		protected $_index;
		protected $_query;
		protected $_args;
		protected $_options;

		public function __construct(parent $query, array $options, $args = null)
		{
			$this->_index = $options['index'];
			$this->_options = $options;
			$this->_query = $query;
			$this->_options['count'] = isset($this->_options['count']) ? $this->_options['count'] : $this->_query->_configuration->limit;
			$this->_args = $args;
			$this->_session = \CRUDsader\Session::useNamespace('\\CRUDsader\\Query\\Pagination\\' . $this->_index);
			if (!isset($session->numRows)) {
				$this->_session->numRows = \CRUDsader\Instancer::getInstance()->database->countSelect($query->_sql);
				$this->_session->start = 0;
			}
			if (isset($_REQUEST[$this->_index]))
				$this->_session->start = $_REQUEST[$this->_index] > 0 ? $_REQUEST[$this->_index] : 0;
		}

		public function getIndex()
		{
			return $this->_index;
		}

		public function getObjects()
		{
			$this->_query->_sql['limit'] = array('from' => isset($this->_session->start) ? $this->_session->start : 0, 'count' => $this->_options['count']);
			$results = \CRUDsader\Instancer::getInstance()->database->select($this->_query->_sql, $this->_args);
			return new \CRUDsader\Object\Collection\Initialised($this->_query->_class, $results, $this->_query->_mapFields);
		}

		public function getPage()
		{
			return $this->_session->numRows == 1 ? 1 : floor($this->_session->start / $this->_options['count']) + 1;
		}

		public function getPagesCount()
		{
			return floor($this->_session->numRows / $this->_options['count']);
		}

		// pages + prev / next
		public function getAllPages($max = 10)
		{
			$pages = array();
			if ($this->hasFirstPage()) {
				$pages[] = $this->getFirstPage();
			}
			if ($this->hasPreviousPage()) {
				$pages[] = $this->getPreviousPage();
			}
			$all = $this->getPages($max);
			foreach ($all as $page) {
				$pages[] = $page;
			}
			if ($this->hasNextPage()) {
				$pages[] = $this->getNextPage();
			}
			if ($this->hasLastPage()) {
				$pages[] = $this->getLastPage();
			}
			return $pages;
		}

		public function getPages($max = 10)
		{
			$actual = $this->getPage(); //1
			$nb = $this->getPagesCount(); // 23
			$half = ceil($max / 2); // 5
			$pages = array();
			for ($i = $actual - $half + 1; $i < ($actual + ($actual > $half ? $half + 1 : $max - $actual + 1)); $i++) {
				if ($i > 0)
					$pages[$i] = array('actual' => $i == $actual, 'text' => $i, 'start' => ($i - 1) * $this->_options['count'], 'index' => $this->_index);
				if ($i == $nb)
					break;
			}
			return $pages;
		}

		public function hasNextPage()
		{
			return $this->getPage() < $this->getPagesCount();
		}

		public function hasPreviousPage()
		{
			return $this->getPage() > 1;
		}

		public function getNextPage()
		{
			$actual = $this->getPage();
			return array('actual' => false, 'text' => '>', 'start' => ($actual) * $this->_options['count'], 'index' => $this->_index);
		}

		public function getPreviousPage()
		{
			$actual = $this->getPage();
			return array('actual' => false, 'text' => '<', 'start' => ($actual - 2) * $this->_options['count'], 'index' => $this->_index);
		}

		public function hasFirstPage()
		{
			return $this->getPage() > 1;
		}

		public function getFirstPage()
		{
			return array('actual' => false, 'text' => '<<', 'start' => 0, 'index' => $this->_index);
		}

		public function hasLastPage()
		{
			$count = $this->getPagesCount();
			return $count > 1 && $count != $this->getPage();
		}

		public function getLastPage()
		{
			$count = $this->getPagesCount();
			return array('actual' => false, 'text' => '>>', 'start' => ($count - 1) * $this->_options['count'], 'index' => $this->_index);
		}
	}
	class PaginationException extends \CRUDsader\Exception {
		
	}
}