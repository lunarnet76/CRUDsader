<?php
/**
 * Art Framework
 *
 * LICENSE
 * see LICENSE.txt.
 *
 * @category   
 * @package    
 * @copyright 
 * @license   
 * @version    
 */
/**
 * The idea is to identify any object by a unique identifier : high id + low id, high id is calculated from the database once the people is logged, the low id starts at 0 and goes incrementaly
 */
class Art_Adapter_Implementation_Identifier_Default extends Art_Adapter_Abstract {
    /**
     * the $_SESSION object linked to this class
     * @var Zend_Session_Namespace
     */
    protected $_session = NULL;
    /**
     * the $_SESSION object linked to this class
     * @var Zend_Session_Namespace
     */
    protected $_highIdLength = 10;
    /**
     * the $_SESSION object linked to this class
     * @var Zend_Session_Namespace
     */
    protected $_implementationHighId = 'default';
    /**
     * the $_SESSION object linked to this class
     * @var Zend_Session_Namespace
     */
    protected $_length = 20;
    protected $_nextId = false;
    protected $_classes = array();

    /**
     * constructor
     * @access private
     * @return void
     */
    public function init() {
        $configuration = Art_Configuration::getInstance();
        if (isset($configuration->mapper->identifier->highIdLength))
            $this->_highIdLength = $configuration->mapper->identifier->highIdLength;
        if (isset($configuration->mapper->identifier->length))
            $this->_length = $configuration->mapper->identifier->length;
        $this->_session = Art_Session::useNamespace('Art_Object_Identifier');
        $this->_session->highId = isset($this->_session->highId) ? $this->_session->highId : $this->_getNewHighId();
        if (!isset($this->_session->lowId))
            $this->_session->lowId = array();
        $this->_classes = Art_Mapper::getInstance()->toArray();
    }

    public function getLength() {
        return $this->_length;
    }
    
    public function getHighIdLength(){
        return $this->_highIdLength;
    }

    /**
     * return a unique Object Identifier
     * @access public
     * @return string
     */
    public function getOID($class) {
        if ($this->_nextId) {
            $oid = $this->_nextId;
            $this->_nextId = false;
            return $oid;
        }
        if (!isset($this->_session->lowId->$class))
            $this->_session->lowId->$class = 0;
        $this->_session->lowId->$class = $this->_session->lowId->$class + 1;
        $oid = $this->_session->lowId->$class . $this->_session->highId;
        return $oid;
    }

    public function setNextId($id) {
        $this->_nextId = $id;
    }

    /**
     * return a new high id
     * @access private
     * @return string
     */
    protected function _getNewHighId() {
        $this->_highId = date('dmyhis');
        if ($this->_highIdLength) {
            $length = strlen($this->_highId);
            if ($length > $this->_highIdLength)
                $this->_highId = substr($this->_highId, 0, $this->_highIdLength);
            else {
                for ($i = 0; $i < $this->_highIdLength - $length; $i++)
                    $this->_highId = '0' . $this->_highId;
            }
        }
        return $this->_highId;
    }

    /**
     * return a singletoned instance
     * @access public
     * @return void
     * @static
     */
    public static function getInstance() {
        return parent::getInstanceOf(__CLASS__);
    }
}
?>