<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/2.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art\Adapter\Identifier {
    /**
     * @package    Art\Adapter\Identifier
     */
    class Hilo extends \Art\Adapter {
        /**
         * @var \Art\Session
         */
        protected $_session = NULL;

        /**
         * constructor
         */
        public function init() {
            $this->_session = \Art\Session::useNamespace('Art\\Adapter\\Identifier');
            $this->_session->highId = isset($this->_session->highId) ? $this->_session->highId : $this->_getNewHighId();
            if (!isset($this->_session->lowId))
                $this->_session->lowId = array();
        }

        /**
         * return a unique Object Identifier
         * @access public
         * @return string
         */
        public function getOID($class) {
            if (!isset($this->_session->lowId->$class))
                $this->_session->lowId->$class = 0;
            $this->_session->lowId->$class++;
            $oid = $this->_session->lowId->$class . $this->_session->highId;
            return $oid;
        }

        /**
         * return a new high id
         * @access private
         * @return string
         */
        protected function _getNewHighId() {
            $highId = date('dmyhis');
            $highIdLength = $this->_configuration->highIdLength;
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