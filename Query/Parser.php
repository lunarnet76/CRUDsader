<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       18/03/2012
 */
namespace CRUDsader\Query {
    class Parser {
        public $_parsing;

        public function __construct($oql) {
            $this->_oql = $oql;

            $this->_parsing = array(
                'select' => array(
                    'begin' => array('SELECT', 'select'),
                    'end' => array('FROM', 'from'),
                    'includeEnd' => false,
                    'elements' => array(
                        '*',
                    )
                )
            );
        }
    }
}
