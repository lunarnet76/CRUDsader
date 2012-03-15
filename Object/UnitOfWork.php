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
        protected $_registered = array();
        
        public function register($class,$id){
            if(isset($this->_registered[$class][$id]))
                    return false;
            $this->_registered[$class][$id]=true;
            return true;
        }

        public function insert($table, array $params, $object = null) {
            $this->_transactions[] = array('insert' => array($table, $params,$object));
        }

        public function update($table, array $params, $where=false) {
            $this->_transactions[] = array('update' => array($table, $params, $where));
        }

        public function delete($table, $id) {
            $this->_transactions[] = array('delete' => array($table, $id));
        }

        public function execute() {
            if (empty($this->_transactions))
                return;
            $database = \CRUDsader\Instancer::getInstance()->database;
            //$database->setForeignKeyCheck(false);
            $database->beginTransaction();
            $lastExecuted = false;
            try {
                foreach ($this->_transactions as $id => $transaction) {
                    $params = current($transaction);
                    if (key($transaction) == 'insert'){
                        $database->insert($params[0], $params[1]);
			if(isset($params[2])){
				$params[2]->setId($database->last_insert_id());
			}
		    }elseif (key($transaction) == 'delete')
                        $database->delete($params[0], $params[1]);
                    else
                        $database->update($params[0], $params[1], $params[2]);
                    $lastExecuted = $id;
                }
                $database->commit();
                //$database->setForeignKeyCheck(true);
            } catch (Exception $e) {
                $database->rollBack();
                //$database->setForeignKeyCheck(true);
                throw new UnitOfWorkException('transaction failed : ' . $e->getMessage());
            }
        }
    }
    class UnitOfWorkException extends \CRUDsader\Exception {
        
    }
}
