<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object {
	class UnitOfWork {
		protected $_transactions = array();
		protected $_transaction = false;
		protected $_transacts = false;
		protected $_fail = false;
		protected $_registered = array();

		public function register($class, $id)
		{
			if (isset($this->_registered[$class][$id]))
				return false;
			$this->_registered[$class][$id] = true;
			return true;
		}

		public function insert($table, array $params, $object = null)
		{
			$this->_transaction = array($table, $params, $object);
			$this->_transactions[] = array($table, $params);
			$this->transact('insert');
		}

		public function update($table, array $params, $where = false)
		{
			$this->_transactions[] = $this->_transaction = array($table, $params, $where);
			$this->transact('update');
		}

		public function delete($table, $id)
		{
			$this->_transactions[] = $this->_transaction =  array($table, $id);
			$this->transact('delete');
		}

		public function transact($type)
		{
			$database = \CRUDsader\Instancer::getInstance()->database;
			if (!$this->_transacts) {
				$database->beginTransaction();
				$this->_transacts = true;
			}
			if (!$this->_transaction)
				return;
			try{
				$params = $this->_transaction;
				
				if ($type == 'insert') {
					$database->insert($params[0], $params[1]);
					if (isset($params[2])) {
						$params[2]->setId($database->last_insert_id());
					}
				} elseif ($type == 'delete')
					$database->delete($params[0], $params[1]);
				else
					$database->update($params[0], $params[1], $params[2]);
			}catch (Exception $e) {
				$this->_fail = true;
				$database->rollBack();
				throw new UnitOfWorkException('transaction failed : ' . $e->getMessage());
			}
		}

		public function commit()
		{
			if(!$this->_fail && $this->_transacts){
				\CRUDsader\Instancer::getInstance()->database->commit();
				$this->_transacts = false;
				$this->_fail = false;
			}
		}
	}
	class UnitOfWorkException extends \CRUDsader\Exception {
		
	}
}
