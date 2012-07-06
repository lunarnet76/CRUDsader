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
		protected $_cacheManager = false;
		protected $_engine = 'file';

		public function __construct()
		{
			parent::__construct();

			// cache
			$this->_cacheManager = new \Zend_Cache_Manager;
			foreach ($this->_configuration as $name => $cacheInfos) {
				$this->_cacheManager->setCacheTemplate($name, $cacheInfos->toArray());
			}
		}

		public function get($index, $fixture)
		{
			if ($this->_cacheManager) {
				$cache = $this->_cacheManager->getCache($this->_engine);
				if (false === $data = $cache->load($index)) {
					$data = $fixture();
					$cache->save($data);
				}
				return $data;
			}
			else
				return $fixture();
		}
	}
}