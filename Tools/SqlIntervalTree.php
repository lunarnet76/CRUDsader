<?php
namespace CRUDsader\Tools {
	class SqlIntervalTree {
		public $_inf;
		public $_sup;
		public $_parent;
		public $_depth;

		public function __construct($inf = 'inf', $sup = 'sup', $parent = 'parent', $depth = 'depth')
		{
			$this->_inf = $inf;
			$this->_sup = $sup;
			$this->_parent = $parent;
			$this->_depth = $depth;
		}
		
		public function setAsRoot(\CRUDsader\Object $object){
			$object->inf=1;
			$object->sup=2;
			$object->depth=0;
			$object->save();
		}

		public function insert(\CRUDsader\Object $object, \CRUDsader\Object $root = null)
		{
			$db = \CRUDsader\Instancer::getInstance()->database();
			// if added to a node
			if (isset($root)) {
				$db->query('UPDATE ' . $object->getDatabaseTable() . ' SET ' . $this->_inf . '=' . $this->_inf . '+2 WHERE ' . $this->_inf . '>=' . $root->{$this->_sup}, 'update');
				$db->query('UPDATE ' . $object->getDatabaseTable() . ' SET ' . $this->_sup . '=' . $this->_sup . '+2 WHERE ' . $this->_sup . '>=' . $root->{$this->_sup}, 'update');

				
				$inf = $root->{$this->_sup};
				$sup = $root->{$this->_sup} + 1;

				$root->{$this->_sup}+=2;
			} else {
				// the inf 
				$node = $db->query('SELECT MAX(sup) themax FROM ' . $object->getDatabaseTable());

				// if table is empty then this is a root
				if (!empty($node[0])) {
					$db->query('UPDATE ' . $object->getDatabaseTable() . ' SET ' . $this->_sup . '=' . $this->_sup . '+2 WHERE ' . $this->_inf . '=1', 'update');
					$node = array(0);
				}
				$inf = $node[0] + 1;
				$sup = $node[0] + 2;
				if ($this->_depth)
					$object->{$this->_depth} = 0;
			}

			$object->{$this->_inf} = $inf;
			$object->{$this->_sup} = $sup;


			if (isset($root)) {
				if ($this->_parent)
					$object->{$this->_parent}[] = $root->getId();

				if ($this->_depth)
					$object->{$this->_depth} = $root->{$this->_depth} + 1;
				$root->save();
			}
			$object->save();
		}
		
		public function delete(\CRUDsader\Object $object){
			// la region
			if($object->{$this->_sup} - $object->{$this->_inf} != 1)
				throw new SqlIntervalTreeException('you can delete only leaves');
			$db = \CRUDsader\Instancer::getInstance()->database();
			
			$db->query('UPDATE ' . $object->getDatabaseTable() . ' SET ' . $this->_sup . '=' . $this->_sup . '-2 WHERE '.$this->_sup.'>'.$object->{$this->_sup});
			$db->query('UPDATE ' . $object->getDatabaseTable() . ' SET ' . $this->_inf . '=' . $this->_inf . '-2 WHERE '.$this->_inf.'>'.$object->{$this->_sup});
			
			$object->delete();
			
		}
	}
	
	class SqlIntervalTreeException extends \CRUDsader\Exception{}
}
