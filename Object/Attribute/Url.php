<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       04/04/2012
 */
namespace CRUDsader\Object\Attribute {
    class Url extends \CRUDsader\Object\Attribute {

        

        public function isValid() {
            if (true!== $error = parent::isValid())
				return $error;
	    if(isset($this->_options['lookup']) && $this->_options['lookup']){
		    $headers = get_headers($this->_value);
		    if(strpos($headers[0],'200')===false)return 'not_found';
	    }
            return true;
        }
    }
}
