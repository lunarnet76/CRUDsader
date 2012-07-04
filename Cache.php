<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
	class Cache extends MetaClass {
		/**
		 * @var string
		 */
		protected $_classIndex = 'cache';
		protected $_engine = 'file';

		public function __construct()
		{
			parent::__construct();

			require_once('Zend/Cache.php');
			require_once('Zend/Cache/Manager.php');
			// cache
			$this->_cacheManager = new \Zend_Cache_Manager;
			foreach ($this->_configuration as $name => $cacheInfos) {
				$this->_cacheManager->setCacheTemplate($name, $cacheInfos->toArray());
			}
		}

		public function __get($name)
		{
			if ($name == 'void') {
				$this->_engine = false;
				return $this;
			}
			if (!$this->_cacheManager->hasCache($name))
				throw new \Exception('no engine "' . $name . '"');
			$this->_engine = $name;
			return $this;
		}

		public function get($index)
		{
			if (!$this->_engine)
				return false;
			return $this->_cacheManager->getCache($this->_engine)->load($index);
		}

		public function set($index, $data)
		{
			if ($this->_engine)
				$this->_cacheManager->getCache($this->_engine)->save($data);
		}

		public function test()
		{
			$cache = sl()->cache->memory;
			if (false !== $data = $cache->get('mycache1')) {
				$data = array('test' => 'cool');
				$cache->set('mycache1', $data);
			}
			var_dump($data);
		}
	}
}