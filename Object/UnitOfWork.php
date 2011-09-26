<?php
namespace CRUDsader\Object {
    class UnitOfWork {
        protected $_transactions = array();

        public function insert($table, array $params) {
            $this->_transactions[] = array('insert' => array($table, $params));
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
            $database = \CRUDsader\Database::getInstance();
            $database->beginTransaction();
            $lastExecuted = false;
            try {
                foreach ($this->_transactions as $id => $transaction) {
                    $params = current($transaction);
                    if (key($transaction) == 'insert')
                        $database->insert($params[0], $params[1]);
                    elseif (key($transaction) == 'delete')
                        $database->delete($params[0], $params[1]);
                    else
                        $database->update($params[0], $params[1], $params[2]);
                    $lastExecuted = $id;
                }
                $database->commit();
            } catch (Exception $e) {
                $database->rollBack();
                throw new UnitOfWorkException('transaction failed : ' . $e->getMessage());
            }
        }
    }
    class UnitOfWorkException extends \CRUDsader\Exception{
        
    }
}
