<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter\Identifier {
    /**
     * use the HiLo concept to return unique object identifier
     * @package    CRUDsader\Adapter\Identifier
     */
    class Hilo extends \CRUDsader\Adapter\Identifier {
        /**
         * @var \CRUDsader\Session
         */
        protected $_session = NULL;

        /**
         * constructor
         */
        public function init() {
            $this->_session = \CRUDsader\Session::useNamespace('CRUDsader\\Adapter\\Identifier\\Hilo');
            $this->_session->highId = isset($this->_session->highId) ? $this->_session->highId : $this->_getNewHighId();
            if (!isset($this->_session->lowId))
                $this->_session->lowId = array();
        }

        /**
         * return a unique Object Identifier
         * @access public
         * @return string
         */
        public function getOID($classInfos) {
            if (!isset($this->_session->lowId->{$classInfos['class']}))
                $this->_session->lowId->{$classInfos['class']} = 0;
            $this->_session->lowId->{$classInfos['class']}++;
            $oid = $this->_session->lowId->{$classInfos['class']} . $this->_session->highId;
            return $oid;
        }

        /**
         * return a new high id
         * @access private
         * @return string
         */
        protected function _getNewHighId() {
            $highId = date('ysdmhi');
            $highIdLength = 12;
            $length = strlen($highId);
            if ($length > $highIdLength)
                $highId = substr($highId, 0, $highIdLength);
            else {
                for ($i = 0; $i < $highIdLength - $length; $i++)
                    $highId = '0' . $highId;
            }
            return $highId;
        }
    }
}